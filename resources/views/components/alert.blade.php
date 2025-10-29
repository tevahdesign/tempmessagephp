@props([
    "type" => "success",
    "dismissible" => false,
    "title" => null,
    "message" => null,
])

@php
    $alertClasses = [
        "success" => "bg-green-50 border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400",
        "error" => "bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400",
        "warning" => "bg-yellow-50 border-yellow-200 text-yellow-800 dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-400",
        "info" => "bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400",
    ];

    $iconClasses = [
        "success" => "text-green-400",
        "error" => "text-red-400",
        "warning" => "text-yellow-400",
        "info" => "text-blue-400",
    ];

    $icons = [
        "success" => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
        "error" => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>',
        "warning" => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
        "info" => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>',
    ];
@endphp

<div {{ $attributes->merge(["class" => "rounded-xl border p-4 " . $alertClasses[$type]]) }} x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
    <div class="flex">
        <div class="flex-shrink-0">
            <div class="{{ $iconClasses[$type] }}">
                {!! $icons[$type] !!}
            </div>
        </div>
        <div class="ml-3 flex-1">
            @if ($title)
                <h3 class="text-sm font-medium mb-1">
                    {{ __($title) }}
                </h3>
            @endif

            <div class="text-sm">
                @if ($message)
                    <p>{{ __($message) }}</p>
                @else
                    {{ $slot }}
                @endif
            </div>
        </div>

        @if ($dismissible)
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button type="button" @click="show = false" class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200 {{ $type === "success" ? "hover:bg-green-100 focus:ring-green-600 dark:hover:bg-green-800/30" : "" }} {{ $type === "error" ? "hover:bg-red-100 focus:ring-red-600 dark:hover:bg-red-800/30" : "" }} {{ $type === "warning" ? "hover:bg-yellow-100 focus:ring-yellow-600 dark:hover:bg-yellow-800/30" : "" }} {{ $type === "info" ? "hover:bg-blue-100 focus:ring-blue-600 dark:hover:bg-blue-800/30" : "" }}">
                        <span class="sr-only">Dismiss</span>
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
