@if ($paginator->hasPages())
    <nav class="sg-pagination" role="navigation" aria-label="صفحه‌بندی محصولات">
        <div class="sg-pagination__inner">
            <div class="sg-pagination__pages">
                {{-- Previous page --}}
                @if ($paginator->onFirstPage())
                    <span class="sg-pagination__control is-disabled" aria-disabled="true">
                        <span aria-hidden="true">→</span>
                        قبلی
                    </span>
                @else
                    <a class="sg-pagination__control" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <span aria-hidden="true">→</span>
                        قبلی
                    </a>
                @endif

                {{-- Pagination elements --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="sg-pagination__ellipsis" aria-disabled="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="sg-pagination__number is-current" aria-current="page">
                                    <span class="sr-only">صفحه </span>{{ $page }}
                                </span>
                            @else
                                <a class="sg-pagination__number" href="{{ $url }}" aria-label="رفتن به صفحه {{ $page }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next page --}}
                @if ($paginator->hasMorePages())
                    <a class="sg-pagination__control" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        بعدی
                        <span aria-hidden="true">←</span>
                    </a>
                @else
                    <span class="sg-pagination__control is-disabled" aria-disabled="true">
                        بعدی
                        <span aria-hidden="true">←</span>
                    </span>
                @endif
            </div>

            <p class="sg-pagination__summary">
                صفحه {{ number_format($paginator->currentPage()) }}
                از {{ number_format($paginator->lastPage()) }}
            </p>
        </div>
    </nav>
@endif
