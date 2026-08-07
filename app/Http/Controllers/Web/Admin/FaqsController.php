<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Admin\FaqCreateRequest;
use App\Http\Requests\Web\Admin\FaqUpdateRequest;
use App\Http\Resources\FaqResource;
use App\Logging\AppLogger;
use App\Services\Admin\FaqsService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Controller for admin FAQ management pages.
 *
 * Includes listing, adding, editing, publishing, and deleting FAQs.
 * Uses FaqsService for data retrieval and pagination.
 */
class FaqsController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly FaqsService $faqsService,
    ) {
        $this->logger->setContext(context: 'AdminFaqsController Web');
    }

    /**
     * Admin FAQ listing page with pagination and search filters.
     */
    public function index(Request $request): Factory|View
    {
        $paginate = $this->faqsService->paginate($request);

        $data = FaqResource::collection($paginate->getCollection())
            ->resolve($request);

        $data = collect($data)->map(
            fn (array $faq) => json_decode(json_encode($faq))
        );

        return view('admin.faqs.index', [
            'data' => $data,
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
     * Admin add FAQ page.
     */
    public function add(): Factory|View
    {
        return view('admin.faqs.add');
    }

    /**
     * Edit FAQ page by FAQ UID.
     */
    public function edit(string $faqUid): Factory|View
    {
        $entity = $this->faqsService->getByUid($faqUid);

        abort_if($entity === null, 404);

        $faq = FaqResource::make($entity)->resolve(request());

        return view('admin.faqs.edit', [
            'entity' => json_decode(json_encode($faq)),
        ]);
    }

    /**
     * Create a new FAQ.
     */
    public function store(FaqCreateRequest $request): RedirectResponse
    {
        $faq = $this->faqsService->create($request->faqPayload());

        $withMessage = $faq
            ? ['success' => 'FAQ created successfully. You can now edit the details.']
            : ['error' => 'Failed to create the FAQ. Please try again.'];

        return redirect()->route('admin.faqs.edit', ['faqUid' => $faq->uid])
            ->with($withMessage);
    }

    /**
     * Handle update usecase.
     */
    public function update(FaqUpdateRequest $request, string $faqUid): RedirectResponse
    {
        $updated = $this->faqsService->update(
            array_merge($request->payload(), [
                'uid' => $faqUid,
            ])
        );

        $withMessage = $updated
            ? ['success' => 'Updated successfully.']
            : ['error' => 'Failed to update the FAQ. Please try again.'];

        return redirect()
            ->route('admin.faqs.edit', ['faqUid' => $faqUid])
            ->with($withMessage);
    }

    /**
     * Toggle a FAQ's published state.
     */
    public function togglePublished(Request $request, string $faqUid): RedirectResponse
    {
        $faq = $this->faqsService->getByUid($faqUid);

        if ($faq === null) {
            return redirect()->route('admin.faqs')->with('error', 'FAQ not found.');
        }

        $isPublished = ! $faq->is_published;

        $this->faqsService->setPublished($faqUid, $isPublished);

        $this->logger->info('Admin toggled FAQ published state', [
            'faq_uid' => $faqUid,
            'is_published' => $isPublished,
        ]);

        $message = $isPublished
            ? 'FAQ published successfully.'
            : 'FAQ unpublished successfully.';

        $redirectUrl = route('admin.faqs', array_merge($request->query(), []));

        return redirect()
            ->to($redirectUrl . '#faq-row-' . $faqUid)
            ->with('success', $message);
    }

    /**
     * Delete a FAQ.
     */
    public function destroy(Request $request, string $faqUid): RedirectResponse
    {
        try {
            $request->validate([
                'confirmDelete' => 'required|in:DELETE',
            ], [
                'confirmDelete.in' => 'Please type DELETE to confirm FAQ deletion.',
            ]);

            $this->faqsService->delete($faqUid);

            return redirect()
                ->route('admin.faqs')
                ->with('success', 'FAQ deleted successfully.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->logger->error('Error deleting FAQ', [
                'faq_uid' => $faqUid,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to delete the FAQ. Please try again.');
        }
    }
}
