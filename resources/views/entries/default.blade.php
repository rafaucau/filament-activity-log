@php
    $event = $entry->event;
    $causerName = $entry->causer?->name ?? null;
    $fallbackTitle = Str::headline($event);
    $title = $entry->title ?? $fallbackTitle;
    $description = $entry->description
        ?? ($causerName
            ? sprintf('%s %s', $causerName, Str::lower($fallbackTitle))
            : $fallbackTitle);
@endphp

<div
    class="relative grid grid-cols-[1fr_auto] items-start gap-x-3 rounded-md py-2 pe-2 ps-6 transition hover:bg-gray-50/60 dark:hover:bg-white/[0.03]"
    data-type="{{ $entry->type }}"
    data-event="{{ $event }}"
>
    <span class="absolute -start-3 top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-gray-600 ring-4 ring-white dark:bg-white/5 dark:text-gray-300 dark:ring-gray-900">
        <x-filament::icon icon="ri-circle-line" class="h-3.5 w-3.5" />
    </span>

    <div class="min-w-0">
        <p class="text-[13px] leading-5 text-gray-900 dark:text-gray-100">
            <span class="font-medium">{{ $title }}</span>
        </p>
        @if ($description)
            <p class="mt-0.5 text-[12px] text-gray-500 dark:text-gray-400">
                {{ $description }}
            </p>
        @endif
    </div>

    <div class="flex items-center pt-0.5">
        <time
            class="text-[11px] text-gray-500 dark:text-gray-400 tabular-nums"
            datetime="{{ $entry->occurredAt->toIso8601String() }}"
            title="{{ $entry->occurredAt->toDayDateTimeString() }}"
        >
            {{ $entry->occurredAt->diffForHumans(syntax: null, short: true) }}
        </time>
    </div>
</div>
