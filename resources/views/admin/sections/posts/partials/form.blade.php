@php
    $post = $entity ?? null;
    $isEdit = isset($post) && $post !== null;
    $method = strtoupper($method ?? ($isEdit ? 'PUT' : 'POST'));

    $titleValue = old('title', $post->title ?? '');
    $slugValue = old('slug', $post->slug ?? '');
    $typeValue = old('type', $post->type ?? 'blog');
    $dateValue = old('date', $post->date ?? now()->format('Y-m-d'));
    $authorValue = old('author', $post->author ?? 'Classer Media');
    $descriptionValue = old('description', $post->description ?? '');
    $thumbnailValue = old('thumbnail', $post->thumbnail ?? './thumbnail.jpg');
    $altValue = old('alt', $post->alt ?? '');
    $markdownValue = old('markdown', $post->markdown ?? '');

    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helpClass = 'mt-1 text-xs leading-5 text-slate-500';
    $errorClass = 'mt-1 text-sm text-rose-700';
    $inputBaseClass = 'mt-1 w-full rounded-xl border px-3 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[var(--admin-primary)] focus:outline-none focus:ring-4 focus:ring-[var(--admin-primary)]/10';
    $sectionClass = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm mb-4';
    $sectionTitleClass = 'text-base font-bold text-slate-900';
    $sectionDescriptionClass = 'mt-1 text-sm text-slate-500';
@endphp

<section x-data="{ activeTab: 'metadata' }" class="admin-card max-w-5xl overflow-hidden h-full flex flex-col">
    <div class="border-b border-slate-200 bg-white mb-4">
        <nav class="flex gap-6" aria-label="Post editor tabs">
            <button
                type="button"
                x-on:click="activeTab = 'metadata'"
                class="border-b-2 px-1 pb-3 text-sm font-semibold transition"
                x-bind:class="activeTab === 'metadata'
                    ? 'border-[var(--admin-primary)] text-[var(--admin-primary)]'
                    : 'border-transparent text-slate-500 hover:text-slate-800'"
            >
                Metadata
            </button>

            <button
                type="button"
                x-on:click="activeTab = 'content'"
                class="border-b-2 px-1 pb-3 text-sm font-semibold transition"
                x-bind:class="activeTab === 'content'
                    ? 'border-[var(--admin-primary)] text-[var(--admin-primary)]'
                    : 'border-transparent text-slate-500 hover:text-slate-800'"
            >
                Markdown
            </button>
        </nav>
    </div>

    <form class="flex-1 overflow-x-hidden overflow-y-auto" method="POST" action="{{ $action }}" novalidate>
        @csrf

        @if (! in_array($method, ['GET', 'POST'], true))
            @method($method)
        @endif

        <div x-show="activeTab === 'metadata'" x-cloak>
            <div class="{{ $sectionClass }}">
                <div class="mb-5 flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="{{ $sectionTitleClass }}">Post metadata</h3>
                        <p class="{{ $sectionDescriptionClass }}">
                            These fields are written to <span class="font-mono">metadata.json</span> and the public slug also syncs to <span class="font-mono">posts-slug-mapper.txt</span>.
                        </p>
                    </div>
                </div>

                @if ($isEdit)
                    <div class="mb-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="{{ $labelClass }}" for="uid">Post ID</label>
                            <input id="uid" type="text" value="{{ $post->uid }}" class="{{ $inputBaseClass }} border-slate-300 bg-slate-100 font-mono" readonly>
                            <p class="{{ $helpClass }}">Storage folder ID on S3.</p>
                        </div>

                        <div>
                            <label class="{{ $labelClass }}" for="permalink">Public URL</label>
                            <input id="permalink" type="text" value="{{ $post->permalink ?? '' }}" class="{{ $inputBaseClass }} border-slate-300 bg-slate-100" readonly>
                            <p class="{{ $helpClass }}">Resolved from post type and mapper slug.</p>
                        </div>
                    </div>
                @endif

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}" for="title">Title <span class="text-rose-600">*</span></label>
                        <input id="title" name="title" type="text" maxlength="255" required value="{{ $titleValue }}" class="{{ $inputBaseClass }} {{ $errors->has('title') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}" placeholder="Complete guide to GPS in action cameras (2026)">
                        @error('title')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}" for="slug">Public slug <span class="text-rose-600">*</span></label>
                        <input id="slug" name="slug" type="text" maxlength="255" required value="{{ $slugValue }}" class="{{ $inputBaseClass }} {{ $errors->has('slug') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}" placeholder="complete-guide-to-gps-in-action-cameras-2026">
                        <p class="{{ $helpClass }}">Lowercase letters, numbers, and hyphens only.</p>
                        @error('slug')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}" for="type">Type <span class="text-rose-600">*</span></label>
                        <select id="type" name="type" class="{{ $inputBaseClass }} {{ $errors->has('type') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}">
                            <option value="blog" @selected($typeValue === 'blog')>Blog</option>
                            <option value="story" @selected($typeValue === 'story')>Story</option>
                        </select>
                        @error('type')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}" for="date">Publish date <span class="text-rose-600">*</span></label>
                        <input id="date" name="date" type="date" required value="{{ $dateValue }}" class="{{ $inputBaseClass }} {{ $errors->has('date') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}">
                        @error('date')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}" for="author">Author <span class="text-rose-600">*</span></label>
                        <input id="author" name="author" type="text" maxlength="255" required value="{{ $authorValue }}" class="{{ $inputBaseClass }} {{ $errors->has('author') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}" placeholder="Classer Media">
                        @error('author')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}" for="thumbnail">Thumbnail path <span class="text-rose-600">*</span></label>
                        <input id="thumbnail" name="thumbnail" type="text" maxlength="255" required value="{{ $thumbnailValue }}" class="{{ $inputBaseClass }} {{ $errors->has('thumbnail') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}" placeholder="./thumbnail.jpg">
                        <p class="{{ $helpClass }}">Relative to the post folder in S3.</p>
                        @error('thumbnail')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}" for="description">Description</label>
                        <textarea id="description" name="description" rows="3" class="{{ $inputBaseClass }} {{ $errors->has('description') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}" placeholder="Short summary used in listings and SEO cards.">{{ $descriptionValue }}</textarea>
                        @error('description')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="{{ $labelClass }}" for="alt">Alt text</label>
                        <input id="alt" name="alt" type="text" maxlength="255" value="{{ $altValue }}" class="{{ $inputBaseClass }} {{ $errors->has('alt') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}" placeholder="Complete guide to GPS in action cameras (2026)">
                        @error('alt')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'content'" x-cloak>
            <div class="{{ $sectionClass }}">
                <div class="mb-5 border-b border-slate-100 pb-4">
                    <h3 class="{{ $sectionTitleClass }}">Markdown content</h3>
                    <p class="{{ $sectionDescriptionClass }}">
                        This is stored as <span class="font-mono">post.md</span>. Keep any <span class="font-mono">@{{image-path}}</span> and <span class="font-mono">@{{video-path}}</span> placeholders if you need asset-relative embeds.
                    </p>
                </div>

                <div>
                    <label class="{{ $labelClass }}" for="markdown">Markdown <span class="text-rose-600">*</span></label>
                    <textarea id="markdown" name="markdown" rows="26" class="{{ $inputBaseClass }} min-h-[32rem] font-mono {{ $errors->has('markdown') ? 'border-rose-300 bg-rose-50' : 'border-slate-300 bg-white' }}" placeholder="# Heading\n\nWrite the post content here...">{{ $markdownValue }}</textarea>
                    @error('markdown')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="sticky bottom-0 z-10 -mx-5 border-t border-slate-200 bg-white/90 mt-4 px-5 py-4 backdrop-blur supports-[backdrop-filter]:bg-white/75">
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">
                    Required fields are marked with <span class="font-semibold text-rose-600">*</span>.
                </p>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ url('/admin/posts') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                        Cancel
                    </a>

                    <button type="submit" class="inline-flex justify-center items-center py-2 px-4 text-base font-medium text-center text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ $isEdit ? 'Update post' : 'Create post' }}
                    </button>
                </div>
            </div>

            @if ($isEdit)
                <div class="mt-4 border-t border-slate-200 pt-4">
                    @include('admin.partials.confirm-delete-form', [
                        'formId' => 'delete-post-form',
                        'action' => url('/admin/posts/' . $post->uid),
                        'title' => 'Delete this post',
                        'description' => 'This will permanently remove the post folder from S3 and delete its slug mapping entry. This action cannot be undone.',
                        'confirmLabel' => 'Type DELETE to confirm',
                        'confirmValue' => 'DELETE',
                        'buttonLabel' => 'Delete post',
                    ])
                </div>
            @endif
        </div>
    </form>
</section>