@php
    $productUid = $productUid ?? null;
    $productUids = $productUids ?? [];
    $productSku = $productSku ?? null;
    $productSkus = $productSkus ?? [];
    $buttonLabel = $buttonLabel ?? 'Buy Now';
    $formClass = $formClass ?? 'mt-8';
@endphp

@include('partials.shared.catalog-item-purchase-form', [
    'catalogItemUid' => $productUid,
    'catalogItemUids' => $productUids,
    'catalogItemSku' => $productSku,
    'catalogItemSkus' => $productSkus,
    'buttonLabel' => $buttonLabel,
    'formClass' => $formClass,
])