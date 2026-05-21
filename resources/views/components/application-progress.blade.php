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

    $stepClass = function (int $index) use ($statusEnum): array {
        if ($index === 0) {
            return ['bg-amber-300/85', 'text-amber-100'];
        }

        if ($index === 1) {
            return ['bg-yellow-300/85', 'text-yellow-100'];
        }

        if ($index === 2) {
            return ['bg-sky-300/85', 'text-sky-100'];
        }

        return match ($statusEnum) {
            \App\Enums\ApplicationStatus::Accepted => ['bg-emerald-300/85', 'text-emerald-100'],
            \App\Enums\ApplicationStatus::Rejected => ['bg-rose-300/85', 'text-rose-100'],
            \App\Enums\ApplicationStatus::Cancelled => ['bg-slate-300/75', 'text-slate-200'],
            default => ['bg-slate-300/75', 'text-slate-200'],
        };
    };
@endphp

<div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
    @foreach ($steps as $index => $step)
        @php($isActive = $index <= $activeIndex)
        @php($classes = $isActive ? $stepClass($index) : ['bg-white/10', 'text-slate-500'])
        <div class="min-w-0">
            <div class="h-1.5 rounded-full {{ $classes[0] }}"></div>
            <p class="mt-2 truncate text-[11px] font-medium {{ $classes[1] }}">{{ $step }}</p>
        </div>
    @endforeach
</div>
