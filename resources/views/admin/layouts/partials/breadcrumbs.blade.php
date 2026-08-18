@if(isset($breadcrumbs) && count($breadcrumbs))
    <nav
        aria-label="Breadcrumb"
        class="admin-breadcrumbs mb-6"
    >

        @foreach($breadcrumbs as $index => $breadcrumb)

            @if($index > 0)
                <span class="text-[var(--admin-border)]">
                    /
                </span>
            @endif

            @if(isset($breadcrumb['url']) && $index !== array_key_last($breadcrumbs))

                <a href="{{ $breadcrumb['url'] }}">
                    {{ $breadcrumb['label'] }}
                </a>

            @else

                <span class="admin-breadcrumb-current">
                    {{ $breadcrumb['label'] }}
                </span>

            @endif

        @endforeach

    </nav>
@endif
