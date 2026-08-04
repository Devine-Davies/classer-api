@php
    $item = $item ?? null;
    $isEdit = $isEdit ?? false;
    $action = $action ?? route('admin.tutorials-items.create');
    $method = strtoupper($method ?? ($isEdit ? 'PUT' : 'POST'));

    $labelValue = old('label', $item['label'] ?? '');
    $urlValue = old('url', $item['url'] ?? '');
    $thumbnailValue = old('thumbnail', $item['thumbnail'] ?? '');
    $descriptionValue = old('description', $item['description'] ?? '');
    $altValue = old('alt', $item['alt'] ?? '');
    $sortOrderValue = old('sortOrder', $item['sortOrder'] ?? 0);

    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helpClass = 'mt-1 text-xs leading-5 text-slate-500';
    $errorClass = 'mt-1 text-sm text-rose-700';
    $inputBaseClass = 'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[var(--admin-primary)] focus:outline-none focus:ring-4 focus:ring-[var(--admin-primary)]/10';
    $sectionClass = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm mb-4';
    $sectionTitleClass = 'text-base font-bold text-slate-900';
    $sectionDescriptionClass = 'mt-1 text-sm text-slate-500';
@endphp

<form class="flex-1 overflow-x-hidden overflow-y-auto" method="POST" action="{{ $action }}" novalidate>
    @csrf

    @if (! in_array($method, ['GET', 'POST'], true))
        @method($method)
    @endif

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Tutorial content</h3>
            <p class="{{ $sectionDescriptionClass }}">
                The title, video URL, and supporting copy shown on the public guides page.
            </p>
        </div>

        <div class="space-y-5">
            <div>
                <label class="{{ $labelClass }}" for="label">
                    Label <span class="text-rose-600">*</span>
                </label>

                <input
                    id="label"
                    name="label"
                    type="text"
                    maxlength="255"
                    required
                    value="{{ $labelValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('label') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="Importing videos"
                >

                @error('label')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="url">
                    YouTube URL <span class="text-rose-600">*</span>
                </label>

                <input
                    id="url"
                    name="url"
                    type="url"
                    required
                    value="{{ $urlValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('url') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="https://www.youtube.com/watch?v=..."
                >

                @error('url')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="thumbnail">
                    Thumbnail URL <span class="text-rose-600">*</span>
                </label>

                <input
                    id="thumbnail"
                    name="thumbnail"
                    type="url"
                    required
                    value="{{ $thumbnailValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('thumbnail') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="https://classermedia.com/assets/images/..."
                >

                @error('thumbnail')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="{{ $labelClass }}" for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    class="{{ $inputBaseClass }} resize-y {{ $errors->has('description') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="Explain what this tutorial helps the user do."
                >{{ $descriptionValue }}</textarea>

                @error('description')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="{{ $sectionTitleClass }}">Metadata</h3>
            <p class="{{ $sectionDescriptionClass }}">
                Set the alt text and ordering for the tutorial card.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="{{ $labelClass }}" for="alt">
                    Alt text
                </label>

                <input
                    id="alt"
                    name="alt"
                    type="text"
                    maxlength="255"
                    value="{{ $altValue }}"
                    class="{{ $inputBaseClass }} {{ $errors->has('alt') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}"
                    placeholder="Tutorial preview for importing videos into Classer."
                >

                <p class="{{ $helpClass }}">
                    Optional descriptive text for the thumbnail image.
                </p>

                @error('alt')
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

    <div class="sticky bottom-0 z-10 -mx-5 border-t border-slate-200 bg-white/90 mt-4 px-5 py-4 backdrop-blur supports-[backdrop-filter]:bg-white/75">
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500">
                Required fields are marked with
                <span class="font-semibold text-rose-600">*</span>.
            </p>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <a
                    href="{{ route('admin.tutorials-items') }}"
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