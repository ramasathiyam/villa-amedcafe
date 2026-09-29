@props(['amount', 'unit' => null])

<span {{ $attributes->merge(['class' => 'price-badge']) }}>{{ $amount }}{{ $unit ? ' / ' . $unit : '' }}</span>
