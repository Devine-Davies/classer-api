@php
    $catalogItemUid = $catalogItemUid ?? null;
    $catalogItemUids = $catalogItemUids ?? [];
    $catalogItemSku = $catalogItemSku ?? null;
    $catalogItemSkus = $catalogItemSkus ?? [];
    $buttonLabel = $buttonLabel ?? 'Buy Now';
    $formClass = $formClass ?? 'mt-8';
@endphp

<form action="{{ url('/checkout/start') }}" method="POST" class="{{ $formClass }}">
    @csrf
    @if (!empty($catalogItemSkus))
        @foreach ($catalogItemSkus as $bundleCatalogItemSku)
            <input type="hidden" name="catalog_item_skus[]" value="{{ $bundleCatalogItemSku }}">
        @endforeach
    @elseif (!empty($catalogItemUids))
        @foreach ($catalogItemUids as $bundleCatalogItemUid)
            <input type="hidden" name="catalog_item_uids[]" value="{{ $bundleCatalogItemUid }}">
        @endforeach
    @elseif (!empty($catalogItemSku))
        <input type="hidden" name="catalog_item_sku" value="{{ $catalogItemSku }}">
    @else
        <input type="hidden" name="catalog_item_uid" value="{{ $catalogItemUid }}">
    @endif

    <button type="submit" class="btn w-full">{{ $buttonLabel }}</button>
</form>
