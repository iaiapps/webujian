<div class="stat-card {{ $class ?? '' }}">
    <div class="stat-icon {{ $iconVariant ?? 'primary' }}">
        <i class="bi bi-{{ $icon ?? 'info-circle' }}"></i>
    </div>
    <div class="stat-content">
        <div class="stat-value">{{ $value }}</div>
        <div class="stat-label">{{ $label }}</div>
        @if(isset($change))
        <div class="stat-change {{ $changeType ?? 'positive' }}">
            <i class="bi bi-{{ $changeType == 'positive' ? 'arrow-up' : 'arrow-down' }}"></i>
            {{ $change }}
        </div>
        @endif
    </div>
</div>