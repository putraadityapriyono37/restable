@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-charcoal/80']) }}>
    {{ $value ?? $slot }}
</label>
