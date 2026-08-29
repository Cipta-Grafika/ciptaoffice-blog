<?php

namespace App\Services;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Carbon\CarbonImmutable;
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

    private const OPTIONAL_COLUMNS = ['author_name', 'published_at', 'modified_at', 'cover_image_path', 'cover_image_alt'];

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
        'author' => 'author_name',
        'author_name' => 'author_name',
        'penulis' => 'author_name',
        'published_at' => 'published_at',
        'modified_at' => 'modified_at',
        'cover_image_path' => 'cover_image_path',
        'cover_image_alt' => 'cover_image_alt',
        'inline_images' => 'inline_images',
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
                $post = Post::create([
                    'author_id' => $author->id,
                    'author_name' => $row['author_name'],
                    'title' => $row['title'],
                    'slug' => $this->uniqueSlug($row['title']),
                    'excerpt' => $row['excerpt'],
                    'body_html' => $this->sanitizer->clean($row['body_html']),
                    'status' => PostStatus::Published,
                    'cover_image_path' => $row['cover_image_path'],
                    'cover_image_alt' => $row['cover_image_alt'],
                    'published_at' => $this->importDate($row['published_at']),
                    'updated_at' => $this->importDate($row['modified_at']),
                ]);

                foreach ($row['inline_images'] as $media) {
                    $post->media()->create([
                        'uploaded_by' => $author->id,
                        'path' => $media['storage_path'],
                        'alt_text' => ($media['alt_text'] ?? null) ?: $row['title'],
                    ]);
                }
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
     * @return list<array<string, mixed>>
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
     * @return list<array<string, mixed>>
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
     * @return array<string, mixed>
     */
    private function mapJsonItem(array $item): array
    {
        $normalized = [];

        foreach ($item as $key => $value) {
            $column = self::HEADER_ALIASES[$this->normalizeHeader((string) $key)] ?? null;

            if ($column !== null && ! array_key_exists($column, $normalized)) {
                $normalized[$column] = match ($column) {
                    'inline_images' => $value,
                    'author_name' => $this->jsonAuthorName($value),
                    default => $this->jsonFieldValue($column, $value),
                };
            }
        }

        return array_merge(
            array_fill_keys(self::REQUIRED_COLUMNS, ''),
            array_fill_keys(self::OPTIONAL_COLUMNS, null),
            ['inline_images' => []],
            $normalized,
        );
    }

    private function jsonFieldValue(string $column, mixed $value): string
    {
        if (is_array($value) && array_key_exists('rendered', $value)) {
            $value = $value['rendered'];
        }

        $text = trim($this->stringifyCell($value));

        if (! in_array($column, ['title', 'excerpt'], true)) {
            return $text;
        }

        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    private function jsonAuthorName(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = $value['name'] ?? null;
        }

        $name = trim($this->stringifyCell($value));

        return $name !== '' ? $name : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function validateRow(array $data, string $location): array
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:180'],
            'author_name' => ['nullable', 'string', 'max:120'],
            'excerpt' => ['required', 'string', 'max:1000'],
            'body_html' => ['required', 'string', 'max:100000'],
            'published_at' => ['nullable', 'date'],
            'modified_at' => ['nullable', 'date'],
            'cover_image_path' => ['nullable', 'string', 'regex:#\Aarticles/covers/[A-Za-z0-9][A-Za-z0-9._-]*\.(?:jpe?g|png|webp|gif|avif)\z#i'],
            'cover_image_alt' => ['nullable', 'required_with:cover_image_path', 'string', 'max:180'],
            'inline_images' => ['array', 'max:5000'],
            'inline_images.*.storage_path' => ['required', 'string', 'distinct', 'regex:#\Aarticles/[0-9]+/inline/[A-Za-z0-9][A-Za-z0-9._-]*\.(?:jpe?g|png|webp|gif|avif)\z#i'],
            'inline_images.*.alt_text' => ['nullable', 'string', 'max:180'],
        ], [], [
            'title' => 'title',
            'author_name' => 'author name',
            'excerpt' => 'excerpt',
            'body_html' => 'body_html',
            'published_at' => 'published_at',
            'modified_at' => 'modified_at',
            'cover_image_path' => 'cover_image_path',
            'cover_image_alt' => 'cover_image_alt',
            'inline_images' => 'inline_images',
            'inline_images.*.storage_path' => 'inline image storage_path',
        ]);

        if ($validator->fails()) {
            $this->fail("{$location}: ".$validator->errors()->first());
        }

        $validated = $validator->validated();
        foreach ($validated['inline_images'] as $media) {
            if (! str_contains($validated['body_html'], '/storage/'.$media['storage_path'])) {
                $this->fail("{$location}: body_html tidak mereferensikan inline image {$media['storage_path']}.");
            }
        }

        return $validated;
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
     * @return array<string, mixed>
     */
    private function mapRow(array $headerMap, array $values): array
    {
        $mapped = [];

        foreach (self::REQUIRED_COLUMNS as $column) {
            $mapped[$column] = trim($values[$headerMap[$column]] ?? '');
        }

        foreach (self::OPTIONAL_COLUMNS as $column) {
            $mapped[$column] = isset($headerMap[$column])
                ? (trim($values[$headerMap[$column]] ?? '') ?: null)
                : null;
        }

        $mapped['inline_images'] = [];

        return $mapped;

    }

    private function importDate(?string $value): CarbonImmutable
    {
        if ($value === null || trim($value) === '') {
            return CarbonImmutable::now();
        }

        return CarbonImmutable::parse($value, config('app.timezone'))
            ->setTimezone(config('app.timezone'));
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
