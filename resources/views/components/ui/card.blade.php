<div class="card {{ $class ?? '' }}">
    @if(isset($title) || isset($header))
    <div class="card-header">
        @if(isset($title))
        <h5 class="card-title">{{ $title }}</h5>
        @endif
        @if(isset($header))
        {{ $header }}
        @endif
    </div>
    @endif
    <div class="card-body {{ $bodyClass ?? '' }}">
        {{ $slot }}
    </div>
    @if(isset($footer))
    <div class="card-footer">
        {{ $footer }}
    </div>
    @endif
</div>