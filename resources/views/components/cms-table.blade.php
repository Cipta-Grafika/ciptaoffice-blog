@props([
    'columnCount',
    'empty' => false,
    'emptyIcon' => 'inbox',
    'emptyTitle' => 'Belum ada data',
    'emptyDescription' => null,
    'paginator' => null,
])

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
    <div class="cms-pagination border-top px-3 py-3">
        @if ($paginator->hasPages())
            {{ $paginator->onEachSide(1)->links() }}
        @else
            <nav aria-label="Pagination tabel">
                <ul class="pagination mb-0">
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
@endif
