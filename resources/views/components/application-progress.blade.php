@props(['status'])

@php
    $statusEnum = $status instanceof \App\Enums\ApplicationStatus
        ? $status
        : \App\Enums\ApplicationStatus::tryFrom((string) $status);

    $steps = ['Enviada', 'En revision', 'Entrevista', 'Resultado final'];
    $activeIndex = match ($statusEnum) {
        \App\Enums\ApplicationStatus::Pending => 0,
        \App\Enums\ApplicationStatus::InReview => 1,
        \App\Enums\ApplicationStatus::Interview => 2,
        \App\Enums\ApplicationStatus::Accepted,
        \App\Enums\ApplicationStatus::Rejected,
        \App\Enums\ApplicationStatus::Cancelled => 3,
        default => 0,
    };

    $stepColor = function (int $index) use ($statusEnum): string {
        return match ($index) {
            0 => '#fbbf24',
            1 => '#fde047',
            2 => '#7dd3fc',
            3 => match ($statusEnum) {
                \App\Enums\ApplicationStatus::Accepted => '#6ee7b7',
                \App\Enums\ApplicationStatus::Rejected => '#fda4af',
                default => '#cbd5e1',
            },
            default => '#cbd5e1',
        };
    };
@endphp

<div class="lumoryx-app-progress">
    @foreach ($steps as $index => $step)
        @php
            $isDone = $index < $activeIndex;
            $isCurrent = $index === $activeIndex;
            $isFilled = $index <= $activeIndex;
            $color = $stepColor($index);
        @endphp
        <div class="lumoryx-app-progress-step {{ $isFilled ? 'is-active' : '' }}">
            <div class="lumoryx-app-progress-track {{ $isFilled ? 'is-filled' : '' }}" style="--step-color: {{ $color }};">
                <div class="lumoryx-app-progress-node {{ $isDone ? 'is-done' : ($isCurrent ? 'is-current' : '') }}" style="--step-color: {{ $color }};">
                    @if ($isDone)
                        <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4"><path d="M5 12.5 10 17l9-10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    @else
                        {{ $index + 1 }}
                    @endif
                </div>
            </div>
            <p class="lumoryx-app-progress-label">{{ $step }}</p>
        </div>
    @endforeach
</div>
