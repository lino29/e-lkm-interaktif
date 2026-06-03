@props(['title', 'subtitle' => '', 'actions' => null, 'breadcrumbs' => null])

<header class="canvas-head flex flex-col md:flex-row md:justify-between gap-4 md:items-start mb-6">
    <div class="canvas-title w-full">
        @if($breadcrumbs)
            <nav class="mb-3 flex items-center text-sm font-medium text-elkm-muted" aria-label="Breadcrumb">
                {{ $breadcrumbs }}
            </nav>
        @endif
        <h2 class="m-0 mb-2 text-2xl md:text-3xl font-bold tracking-tight text-elkm-text">{{ $title }}</h2>
        @if($subtitle)
            <p class="m-0 text-elkm-muted max-w-3xl leading-relaxed">{{ $subtitle }}</p>
        @endif
    </div>
    @if($actions)
        <div class="canvas-actions flex gap-2.5 flex-wrap justify-start md:justify-end md:shrink-0 mt-1">
            {{ $actions }}
        </div>
    @endif
</header>
