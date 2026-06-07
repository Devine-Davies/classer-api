@php
    $productUid = $productUid ?? null;
    $productUids = $productUids ?? [];
    $productSku = $productSku ?? null;
    $productSkus = $productSkus ?? [];
    $buttonLabel = $buttonLabel ?? 'Buy Now';
    $formClass = $formClass ?? 'mt-8';
@endphp

<form action="{{ url('/checkout/start') }}" method="POST" class="{{ $formClass }}">
    @csrf
    @if (!empty($productSkus))
        @foreach ($productSkus as $bundleProductSku)
            <input type="hidden" name="product_skus[]" value="{{ $bundleProductSku }}">
        @endforeach
    @elseif (!empty($productUids))
        @foreach ($productUids as $bundleProductUid)
            <input type="hidden" name="product_uids[]" value="{{ $bundleProductUid }}">
        @endforeach
    @elseif (!empty($productSku))
        <input type="hidden" name="product_sku" value="{{ $productSku }}">
    @else
        <input type="hidden" name="product_uid" value="{{ $productUid }}">
    @endif

    <button type="submit" class="btn w-full">{{ $buttonLabel }}</button>
</form>