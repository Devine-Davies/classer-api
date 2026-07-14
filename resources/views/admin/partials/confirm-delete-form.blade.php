@php
    $formId = $formId ?? 'confirm-delete-form';
    $action = $action ?? '#';
    $title = $title ?? 'Delete item';
    $description = $description ?? 'This action cannot be undone.';
    $confirmLabel = $confirmLabel ?? 'Type DELETE to confirm';
    $confirmValue = $confirmValue ?? 'DELETE';
    $buttonLabel = $buttonLabel ?? 'Delete';
@endphp

<section
    x-data="{ open: false, confirmDelete: '' }"
    class="rounded-2xl border border-rose-200 bg-rose-50/60 p-4"
>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-bold text-rose-800">{{ $title }}</h3>
            <p class="mt-1 text-xs leading-5 text-rose-700">{{ $description }}</p>
        </div>

        <button
            type="button"
            x-on:click="open = true"
            class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
        >
            {{ $buttonLabel }}
        </button>
    </div>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-900/50 px-4"
            x-on:keydown.escape.window="open = false"
        >
            <div
                class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl"
                x-on:click.outside="open = false"
            >
                <h4 class="text-base font-bold text-slate-900">Confirm deletion</h4>
                <p class="mt-2 text-sm text-slate-600">{{ $description }}</p>

                <form id="{{ $formId }}" method="POST" action="{{ $action }}" class="mt-4 space-y-4">
                    @csrf
                    @method('DELETE')

                    <div>
                        <label class="block text-sm font-semibold text-slate-700" for="{{ $formId }}-confirmDelete">
                            {{ $confirmLabel }}
                        </label>
                        <input
                            id="{{ $formId }}-confirmDelete"
                            name="confirmDelete"
                            type="text"
                            x-model="confirmDelete"
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-500/10"
                            placeholder="{{ $confirmValue }}"
                            autocomplete="off"
                            required
                        >
                        @error('confirmDelete')
                            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            x-on:click="open = false"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            x-bind:disabled="confirmDelete !== '{{ $confirmValue }}'"
                            class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-rose-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            {{ $buttonLabel }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</section>
