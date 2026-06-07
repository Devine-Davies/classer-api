<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PromotionRedemptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionRedeemController extends Controller
{
    public function form(Request $request): View
    {
        return view('promotions.redeem', [
            'prefillEmail' => (string) $request->query('email', ''),
            'prefillRedeemCode' => (string) $request->query('redeem_code', ''),
        ]);
    }

    public function prefill(string $redeemCode, Request $request): RedirectResponse
    {
        return redirect()->route('promotions.redeem.form', [
            'email' => (string) $request->query('email', ''),
            'redeem_code' => $redeemCode,
        ]);
    }

    public function redeem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'redeem_code' => ['required', 'string', 'regex:/^[A-Za-z0-9]{64}$/'],
        ]);

        $promotionRedemptionService = app(PromotionRedemptionService::class);
        $result = $promotionRedemptionService->redeemFromToken(
            (string) $validated['redeem_code'],
            (string) $validated['email']
        );

        return response()->json($result, 200);
    }
}
