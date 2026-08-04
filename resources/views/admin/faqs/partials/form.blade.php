@php
    $faq = $faq ?? $entity ?? null;

    $isEdit = $isEdit ?? false;
    $action = $action ?? url('/admin/faqs');
    $method = strtoupper($method ?? ($isEdit ? 'PUT' : 'POST'));

    $questionValue = old('question', $faq->question ?? '');
    $answerValue = old('answer', $faq->answer ?? '');
    $categoryValue = old('category', $faq->category ?? '');
    $sortOrderValue = old('sortOrder', $faq->sortOrder ?? 0);
    $isPublishedValue = old('isPublished', $faq->isPublished ?? true);

    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helpClass = 'mt-1 text-xs leading-5 text-slate-500';
    $errorClass = 'mt-1 text-sm text-rose-700';
    $inputBaseClass = 'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[var(--admin-primary)] focus:outline-none focus:ring-4 focus:ring-[var(--admin-primary)]/10';
    $sectionClass = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm mb-4';
    $sectionTitleClass = 'text-base font-bold text-slate-900';
    $sectionDescriptionClass = 'mt-1 text-sm text-slate-500';
@endphp

<section class="admin-card max-w-3xl overflow-hidden h-full flex flex-col">
    <form class="flex-1 overflow-x-hidden overflow-y-auto" method="POST" action="{{ $action }}" novalidate>
        @csrf

        @if (! in_array($method, ['GET', 'POST'], true))
            @method($method)
        @endif

        <div class="{{ $sectionClass }}">
            <div class="mb-5 border-b border-slate-100 pb-4">
                <h3 class="{{ $sectionTitleClass }}">FAQ content</h3>
                <p class="{{ $sectionDescriptionClass }}">
                    The question and answer displayed to visitors on the public site.
                </p>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="{{ $labelClass }}" for="question">
                        Question <span class="text-rose-600">*</span>
                    </label>

                    <input
                        id="question"
                        name="question"
                        type="text"
                        maxlength="500"
                        required
                        value="{{ $questionValue }}"
                        class="{{ $inputBaseClass }} {{ $errors->has('question') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                        placeholder="What exactly is Classer?"
                    >

                    @error('question')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}" for="answer">
                        Answer <span class="text-rose-600">*</span>
                    </label>

                    <textarea
                        id="answer"
                        name="answer"
                        rows="6"
                        required
                        class="{{ $inputBaseClass }} resize-y {{ $errors->has('answer') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                        placeholder="Write the answer shown to customers."
                    >{{ $answerValue }}</textarea>

                    @error('answer')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="{{ $sectionClass }}">
            <div class="mb-5 border-b border-slate-100 pb-4">
                <h3 class="{{ $sectionTitleClass }}">Organisation</h3>
                <p class="{{ $sectionDescriptionClass }}">
                    Group and order how this FAQ appears on the site.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="{{ $labelClass }}" for="category">
                        Category
                    </label>

                    <input
                        id="category"
                        name="category"
                        type="text"
                        maxlength="120"
                        value="{{ $categoryValue }}"
                        class="{{ $inputBaseClass }} {{ $errors->has('category') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                        placeholder="Getting Started"
                    >

                    <p class="{{ $helpClass }}">
                        Optional grouping label shown with the FAQ.
                    </p>

                    @error('category')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}" for="sortOrder">
                        Sort order
                    </label>

                    <input
                        id="sortOrder"
                        name="sortOrder"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ $sortOrderValue }}"
                        class="{{ $inputBaseClass }} {{ $errors->has('sortOrder') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                        placeholder="0"
                    >

                    <p class="{{ $helpClass }}">
                        Lower numbers appear first.
                    </p>

                    @error('sortOrder')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="{{ $sectionClass }}">
            <div class="mb-5 border-b border-slate-100 pb-4">
                <h3 class="{{ $sectionTitleClass }}">Visibility</h3>
                <p class="{{ $sectionDescriptionClass }}">
                    Control whether this FAQ is shown on the public site.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300">
                <input type="hidden" name="isPublished" value="0">

                <label class="flex cursor-pointer items-start gap-3">
                    <input
                        id="isPublished"
                        name="isPublished"
                        type="checkbox"
                        value="1"
                        @checked((bool) $isPublishedValue)
                        class="mt-1 rounded border-slate-300 text-[var(--admin-primary)] focus:ring-[var(--admin-primary)]"
                    >

                    <span>
                        <span class="block text-sm font-semibold text-slate-800">
                            FAQ is published
                        </span>

                        <span class="mt-1 block text-xs leading-5 text-slate-500">
                            Published FAQs appear in the public FAQ section. Unpublish to disable it.
                        </span>
                    </span>
                </label>

                @error('isPublished')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="sticky bottom-0 z-10 -mx-5 border-t border-slate-200 bg-white/90 mt-4 px-5 py-4 backdrop-blur supports-[backdrop-filter]:bg-white/75">
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">
                    Required fields are marked with
                    <span class="font-semibold text-rose-600">*</span>.
                </p>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a
                        href="{{ url('/admin/faqs') }}"
                        class="btn-outline"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn"
                    >
                        {{ $isEdit ? 'Update' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    @if ($isEdit && $faq)
        <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50/60 p-5">
            @include('admin.partials.confirm-delete-form', [
                'action' => route('admin.faqs.destroy', ['faqUid' => $faq->uid]),
                'method' => 'DELETE',
                'title' => 'Delete FAQ',
                'description' => 'This will permanently remove this FAQ from the site.',
                'buttonLabel' => 'Delete FAQ',
                'confirmValue' => 'DELETE',
                'confirmLabel' => 'Type DELETE to confirm',
                'modalTitle' => 'Confirm deletion',
            ])
        </div>
    @endif
</section>
