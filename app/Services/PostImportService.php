<?php

namespace App\Services;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonException;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Throwable;

class PostImportService
{
    private const MAX_ROWS = 1000;

    private const REQUIRED_COLUMNS = ['title', 'excerpt', 'body_html'];

    private const HEADER_ALIASES = [
        'title' => 'title',
        'judul' => 'title',
        'excerpt' => 'excerpt',
        'ringkasan' => 'excerpt',
        'body_html' => 'body_html',
        'body' => 'body_html',
        'content' => 'body_html',
        'konten' => 'body_html',
        'isi' => 'body_html',
    ];

    public function __construct(private readonly HtmlSanitizer $sanitizer) {}

    public function import(UploadedFile $file, User $author): int
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $reader = null;
        $opened = false;

        try {
            if ($extension === 'json') {
                $rows = $this->readJsonRows($file->getRealPath());
            } else {
                $reader = $this->readerFor($file);
                $reader->open($file->getRealPath());
                $opened = true;
                $rows = $this->readSpreadsheetRows($reader);
            }
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->fail('File tidak dapat dibaca. Pastikan file CSV, XLSX, atau JSON tidak rusak dan coba kembali.');
        } finally {
            if ($opened && $reader !== null) {
                $reader->close();
            }
        }

        return DB::transaction(function () use ($rows, $author): int {
            foreach ($rows as $row) {
                Post::create([
                    'author_id' => $author->id,
                    'title' => $row['title'],
                    'slug' => $this->uniqueSlug($row['title']),
                    'excerpt' => $row['excerpt'],
                    'body_html' => $this->sanitizer->clean($row['body_html']),
                    'status' => PostStatus::Draft,
                ]);
            }

            return count($rows);
        });
    }

    private function readerFor(UploadedFile $file): ReaderInterface
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'csv' => new CsvReader($this->csvOptions($file)),
            'xlsx' => new XlsxReader,
            default => $this->fail('Format file harus CSV, XLSX, atau JSON.'),
        };
    }

    private function csvOptions(UploadedFile $file): CsvOptions
    {
        $options = new CsvOptions;
        $options->FIELD_DELIMITER = $this->detectCsvDelimiter($file->getRealPath());

        return $options;
    }

    private function detectCsvDelimiter(string $path): string
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return ',';
        }

        $line = fgets($handle) ?: '';
        fclose($handle);

        $scores = [];
        foreach ([',', ';', "\t"] as $delimiter) {
            $scores[$delimiter] = count(str_getcsv($line, $delimiter));
        }

        arsort($scores);

        return (string) array_key_first($scores);
    }

    /**
     * @return list<array{title: string, excerpt: string, body_html: string}>
     */
    private function readSpreadsheetRows(ReaderInterface $reader): array
    {
        $headerMap = null;
        $rows = [];
        $rowNumber = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowNumber++;
                $values = array_map($this->stringifyCell(...), $row->toArray());

                if ($this->isEmptyRow($values)) {
                    continue;
                }

                if ($headerMap === null) {
                    $headerMap = $this->headerMap($values);

                    continue;
                }

                if (count($rows) >= self::MAX_ROWS) {
                    $this->fail('Maksimum 1.000 artikel dapat diimpor dalam satu file.');
                }

                $data = $this->mapRow($headerMap, $values);
                $rows[] = $this->validateRow($data, "Baris {$rowNumber}");
            }

            break;
        }

        if ($headerMap === null) {
            $this->fail('File import kosong atau tidak memiliki baris header.');
        }

        if ($rows === []) {
            $this->fail('Tidak ada data artikel di bawah baris header.');
        }

        return $rows;
    }

    /**
     * @return list<array{title: string, excerpt: string, body_html: string}>
     */
    private function readJsonRows(string $path): array
    {
        $contents = file_get_contents($path);

        if ($contents === false || trim($contents) === '') {
            $this->fail('File JSON kosong.');
        }

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->fail('Sintaks JSON tidak valid. Periksa kembali tanda kurung, koma, dan tanda kutip.');
        }

        if (! is_array($decoded)) {
            $this->fail('Struktur JSON harus berupa objek artikel atau array artikel.');
        }

        $items = $this->jsonItems($decoded);

        if ($items === []) {
            $this->fail('File JSON tidak memiliki data artikel.');
        }

        if (count($items) > self::MAX_ROWS) {
            $this->fail('Maksimum 1.000 artikel dapat diimpor dalam satu file.');
        }

        $rows = [];

        foreach ($items as $index => $item) {
            $itemNumber = $index + 1;

            if (! is_array($item) || array_is_list($item)) {
                $this->fail("Item JSON {$itemNumber} harus berupa objek artikel.");
            }

            $rows[] = $this->validateRow($this->mapJsonItem($item), "Item JSON {$itemNumber}");
        }

        return $rows;
    }

    /**
     * @param  array<mixed>  $decoded
     * @return list<mixed>
     */
    private function jsonItems(array $decoded): array
    {
        if (array_is_list($decoded)) {
            return $decoded;
        }

        foreach (['articles', 'posts'] as $wrapper) {
            if (array_key_exists($wrapper, $decoded)) {
                if (! is_array($decoded[$wrapper]) || ! array_is_list($decoded[$wrapper])) {
                    $this->fail("Properti JSON '{$wrapper}' harus berupa array artikel.");
                }

                return $decoded[$wrapper];
            }
        }

        return [$decoded];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{title: string, excerpt: string, body_html: string}
     */
    private function mapJsonItem(array $item): array
    {
        $normalized = [];

        foreach ($item as $key => $value) {
            $column = self::HEADER_ALIASES[$this->normalizeHeader((string) $key)] ?? null;

            if ($column !== null && ! array_key_exists($column, $normalized)) {
                $normalized[$column] = trim($this->stringifyCell($value));
            }
        }

        return array_merge(array_fill_keys(self::REQUIRED_COLUMNS, ''), $normalized);
    }

    /**
     * @param  array{title: string, excerpt: string, body_html: string}  $data
     * @return array{title: string, excerpt: string, body_html: string}
     */
    private function validateRow(array $data, string $location): array
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:180'],
            'excerpt' => ['required', 'string', 'max:1000'],
            'body_html' => ['required', 'string', 'max:100000'],
        ], [], [
            'title' => 'title',
            'excerpt' => 'excerpt',
            'body_html' => 'body_html',
        ]);

        if ($validator->fails()) {
            $this->fail("{$location}: ".$validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * @param  list<string>  $headers
     * @return array<string, int>
     */
    private function headerMap(array $headers): array
    {
        $map = [];

        foreach ($headers as $index => $header) {
            $normalized = $this->normalizeHeader($header);
            $column = self::HEADER_ALIASES[$normalized] ?? null;

            if ($column !== null && ! array_key_exists($column, $map)) {
                $map[$column] = $index;
            }
        }

        $missing = array_values(array_diff(self::REQUIRED_COLUMNS, array_keys($map)));

        if ($missing !== []) {
            $this->fail('Kolom wajib belum lengkap: '.implode(', ', $missing).'.');
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $headerMap
     * @param  list<string>  $values
     * @return array{title: string, excerpt: string, body_html: string}
     */
    private function mapRow(array $headerMap, array $values): array
    {
        $mapped = [];

        foreach (self::REQUIRED_COLUMNS as $column) {
            $mapped[$column] = trim($values[$headerMap[$column]] ?? '');
        }

        return $mapped;
    }

    private function normalizeHeader(string $header): string
    {
        return Str::of($header)
            ->replace("\xEF\xBB\xBF", '')
            ->ascii()
            ->lower()
            ->trim()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }

    private function stringifyCell(mixed $value): string
    {
        return match (true) {
            $value === null => '',
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            is_bool($value) => $value ? '1' : '0',
            is_scalar($value) => (string) $value,
            default => '',
        };
    }

    /**
     * @param  list<string>  $values
     */
    private function isEmptyRow(array $values): bool
    {
        return collect($values)->every(fn (string $value): bool => trim($value) === '');
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: Str::random(8);
        $slug = $base;
        $suffix = 2;

        while (Post::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function fail(string $message): never
    {
        $exception = ValidationException::withMessages(['import_file' => $message]);
        $exception->errorBag = 'postImport';

        throw $exception;
    }
}
