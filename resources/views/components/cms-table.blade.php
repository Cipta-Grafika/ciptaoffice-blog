@props([
    'columnCount',
    'empty' => false,
    'emptyIcon' => 'inbox',
    'emptyTitle' => 'Belum ada data',
    'emptyDescription' => null,
    'kicker' => null,
    'paginator' => null,
    'recordLabel' => 'data',
])

@if ($kicker || isset($filters))
    <div class="cms-list-toolbar">
        @if ($kicker)
            <p class="cms-surface-kicker mb-0">{{ $kicker }}</p>
        @endif
        @isset($filters)
            <div class="cms-filter-tool">
                {{ $filters }}
            </div>
        @endisset
    </div>
@endif

<div class="table-responsive">
    <table {{ $attributes->class(['table table-hover cms-table align-middle mb-0']) }}>
        <thead>
            <tr>
                {{ $head }}
            </tr>
        </thead>
        <tbody>
            @if ($empty)
                <tr>
                    <td colspan="{{ $columnCount }}">
                        <div class="cms-empty-state">
                            <span class="cms-empty-state-icon" aria-hidden="true">
                                <i class="bi bi-{{ $emptyIcon }}"></i>
                            </span>
                            <h2>{{ $emptyTitle }}</h2>
                            @if ($emptyDescription)
                                <p class="mb-0">{{ $emptyDescription }}</p>
                            @endif
                        </div>
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>
</div>

@if ($paginator)
    <footer class="cms-table-footer">
        <p class="cms-record-count" aria-live="polite">
            @if ($paginator->total() > 0)
                Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }}
                {{ $recordLabel }}
            @else
                0 {{ $recordLabel }}
            @endif
        </p>
        <div class="cms-pagination cms-pagination--sm">
            @if ($paginator->hasPages())
                {{ $paginator->onEachSide(1)->links() }}
            @else
                <nav aria-label="Pagination tabel">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                        </li>
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">1</span>
                        </li>
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                        </li>
                    </ul>
                </nav>
            @endif
        </div>
    </footer>
@endif
