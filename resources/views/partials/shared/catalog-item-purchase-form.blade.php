@php
    $catalogItemUids = $catalogItemUids ?? [];
    $catalogItemSkus = $catalogItemSkus ?? [];
    $buttonLabel = $buttonLabel ?? 'Buy Now';
    $formClass = $formClass ?? 'mt-8';
@endphp

<form action="{{ url('/checkout/start') }}" method="POST" class="{{ $formClass }}">
    @csrf

    @if (!empty($catalogItemSkus))
        <input type="hidden" name="catalog_item_identifier_type" value="sku">

        @foreach ($catalogItemSkus as $catalogItemSku)
            <input type="hidden" name="catalog_item_skus[]" value="{{ $catalogItemSku }}">
            <input type="hidden" name="quantities[{{ $catalogItemSku }}]" value="1">
        @endforeach
    @elseif (!empty($catalogItemUids))
        <input type="hidden" name="catalog_item_identifier_type" value="uid">

        @foreach ($catalogItemUids as $catalogItemUid)
            <input type="hidden" name="catalog_item_uids[]" value="{{ $catalogItemUid }}">
            <input type="hidden" name="quantities[{{ $catalogItemUid }}]" value="1">
        @endforeach
    @endif

    <button type="submit" class="btn w-full">{{ $buttonLabel }}</button>
</form>