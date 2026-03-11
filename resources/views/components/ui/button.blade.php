<a href="{{ $href ?? '#' }}" class="btn btn-{{ $variant ?? 'primary' }} {{ $class ?? '' }}" {{ isset($disabled) ? 'disabled' : '' }}>
    @if(isset($icon))<i class="bi bi-{{ $icon }}"></i>@endif
    {{ $slot }}
</a>