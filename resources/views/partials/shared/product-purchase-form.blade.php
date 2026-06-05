@php
    $productUid = $productUid ?? null;
    $productUids = $productUids ?? [];
    $buttonLabel = $buttonLabel ?? 'Buy Now';
    $formClass = $formClass ?? 'mt-8';
@endphp

<form action="{{ url('/checkout/start') }}" method="POST" class="{{ $formClass }}">
    @csrf
    @if (!empty($productUids))
        @foreach ($productUids as $bundleProductUid)
            <input type="hidden" name="product_uids[]" value="{{ $bundleProductUid }}">
        @endforeach
    @else
        <input type="hidden" name="product_uid" value="{{ $productUid }}">
    @endif

    <button type="submit" class="btn w-full">{{ $buttonLabel }}</button>
</form>