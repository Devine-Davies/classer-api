@php
    $isEdit = isset($entity) && $entity !== null;
@endphp

<section
    x-data="{ activeTab: 'plan' }"
    class="admin-card max-w-3xl overflow-hidden h-full flex flex-col"
>
    <div class="border-b border-slate-200 bg-white mb-4">
        <nav class="flex gap-6" aria-label="Plan creation tabs">
            <button
                type="button"
                x-on:click="activeTab = 'plan'"
                class="border-b-2 px-1 pb-3 text-sm font-semibold transition"
                x-bind:class="activeTab === 'plan'
                    ? 'border-[var(--admin-primary)] text-[var(--admin-primary)]'
                    : 'border-transparent text-slate-500 hover:text-slate-800'"
            >
                Details
            </button>

            @if ($isEdit)
                <button
                    type="button"
                    x-on:click="activeTab = 'catalog'"
                    class="border-b-2 px-1 pb-3 text-sm font-semibold transition"
                    x-bind:class="activeTab === 'catalog'
                        ? 'border-[var(--admin-primary)] text-[var(--admin-primary)]'
                        : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    Catalog item
                </button>
            @endif
        </nav>
    </div>

    <form class="flex-1 overflow-x-hidden overflow-y-auto" method="POST" action="{{ $action }}" novalidate>
        @csrf

        @if (! in_array($method, ['GET', 'POST', 'HEAD']))
            @method($method)
        @endif

        <div
            x-show="activeTab === 'plan'"
            x-cloak
        >
            @include('admin.sections.products.partials.form-product-body', [
                'entity' => $entity ?? null,
                'isEdit' => $isEdit,
            ])
        </div>

        @if ($isEdit)
            <div
                x-show="activeTab === 'catalog'"
                x-cloak
            >
                @include('admin.partials.form-catalog-item', [
                    'entity' => $entity->catalogItem ?? null,
                    'isEdit' => $isEdit,
                ])
            </div>
        @endif

        <div class="sticky bottom-0 z-10 -mx-5 border-t border-slate-200 bg-white/90 mt-4 px-5 py-4 backdrop-blur supports-[backdrop-filter]:bg-white/75">
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">
                    Required fields are marked with
                    <span class="font-semibold text-rose-600">*</span>.
                </p>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a
                        href="{{ url('/admin/plans') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex justify-center items-center py-2 px-4 text-base font-medium text-center text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        {{ $isEdit ? 'Update' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>