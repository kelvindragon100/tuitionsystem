@props([
    'type' => 'info', // info | success | warning | danger
    'title' => null,
    'message' => null,
])

@php
    $styles = [
        'info'    => 'border-sky-300/70 bg-sky-50 text-sky-800',
        'success' => 'border-emerald-300/70 bg-emerald-50 text-emerald-800',
        'warning' => 'border-amber-300/70 bg-amber-50 text-amber-800',
        'danger'  => 'border-red-300/70 bg-red-50 text-red-800',
    ];
    $icon = [
        'info'    => 'M13 16h-1v-4h-1m1-4h.01',
        'success' => 'M5 13l4 4L19 7',
        'warning' => 'M12 9v4m0 4h.01',
        'danger'  => 'M12 9v4m0 4h.01',
    ][$type] ?? $styles['info'];
@endphp

<div {{ $attributes->merge(['class' => "mb-4 rounded-xl border p-3 flex items-start gap-3 {$styles[$type] }"]) }}>
    <svg class="h-5 w-5 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
        <path d="{{ $icon }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <div>
        @if($title)
            <div class="font-medium">{{ $title }}</div>
        @endif
        @if($message)
            <div class="text-sm opacity-90">{{ $message }}</div>
        @endif
        {{ $slot }}
    </div>
</div>
