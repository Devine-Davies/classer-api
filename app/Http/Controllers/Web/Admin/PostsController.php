<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\PostCreateRequest;
use App\Http\Requests\Web\Admin\PostUpdateRequest;
use App\Logging\AppLogger;
use App\Services\Admin\PostsService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PostsController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly PostsService $postsService,
    ) {
        $this->logger = $logger;
        $this->logger->setContext(context: 'AdminPostsController Web');
    }

    /**
     * Admin posts listing page with pagination and search filters.
     */
    public function index(Request $request): Factory|View
    {
        $paginate = $this->postsService->paginate($request);
        $data = collect($paginate->items())
            ->map(fn (array $post) => json_decode(json_encode($post)));

        return view('admin.posts.index', [
            'data' => $data,
            'cache' => $this->postsService->indexCacheMeta(),
            'filters' => [
                'q' => trim((string) $request->query('q', '')),
            ],
            'pagination' => [
                'total' => $paginate->total(),
                'per_page' => $paginate->perPage(),
                'current_page' => $paginate->currentPage(),
                'last_page' => $paginate->lastPage(),
                'from' => $paginate->firstItem(),
                'to' => $paginate->lastItem(),
            ],
        ]);
    }

    /**
     * Admin add post page.
     */
    public function add(): Factory|View
    {
        return view('admin.posts.add');
    }

    /**
     * Handle create post form submission.
     */
    public function store(PostCreateRequest $request): RedirectResponse
    {
        try {
            $post = $this->postsService->create($request->payload());

            return redirect()
                ->route('admin.posts.edit', ['postUid' => $post['uid']])
                ->with(['success' => 'Post created successfully. You can now edit the metadata and markdown.']);
        } catch (\Throwable $exception) {
            $this->logger->error('Error creating post', [
                'exception' => $exception->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with(['error' => 'Failed to create the post. Please try again.']);
        }
    }

    /**
     * Admin edit post page by post UID.
     */
    public function edit(string $postUid): Factory|View
    {
        $entity = $this->postsService->getByUid($postUid);
        abort_unless($entity !== null, 404);

        return view('admin.posts.edit', [
            'entity' => json_decode(json_encode($entity)),
        ]);
    }

    /**
     * Handle update post form submission.
     */
    public function update(PostUpdateRequest $request, string $postUid): RedirectResponse
    {
        try {
            $this->postsService->update($postUid, $request->payload());

            return redirect()
                ->route('admin.posts.edit', ['postUid' => $postUid])
                ->with(['success' => 'Updated successfully.']);
        } catch (\Throwable $exception) {
            $this->logger->error('Error updating post', [
                'postUid' => $postUid,
                'exception' => $exception->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with(['error' => 'Failed to update the post. Please try again.']);
        }
    }

    /**
     * Run a full S3 scan and rebuild the local posts index cache.
     */
    public function refreshCache(): RedirectResponse
    {
        try {
            $count = $this->postsService->refreshIndexCache();

            return redirect()
                ->route('admin.posts')
                ->with(['success' => 'Posts cache refreshed successfully ('.$count.' posts).']);
        } catch (\Throwable $exception) {
            $this->logger->error('Error refreshing posts cache', [
                'exception' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('admin.posts')
                ->with(['error' => 'Failed to refresh posts cache. Please try again.']);
        }
    }

    /**
     * Delete a post and remove its slug mapper entry.
     */
    public function destroy(Request $request, string $postUid): RedirectResponse
    {
        try {
            $request->validate([
                'confirmDelete' => 'required|in:DELETE',
            ], [
                'confirmDelete.in' => 'Please type DELETE to confirm post deletion.',
            ]);

            $this->postsService->delete($postUid);

            return redirect()
                ->route('admin.posts')
                ->with(['success' => 'Post deleted successfully.']);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->logger->error('Error deleting post', [
                'postUid' => $postUid,
                'exception' => $exception->getMessage(),
            ]);

            return redirect()->back()->with([
                'error' => 'Failed to delete the post. Please try again.',
            ]);
        }
    }
}
