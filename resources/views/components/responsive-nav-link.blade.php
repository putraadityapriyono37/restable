@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-terracotta text-start text-base font-medium text-terracotta-dark bg-terracotta/5 focus:outline-none focus:text-terracotta-dark focus:bg-terracotta/10 focus:border-terracotta transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-charcoal/60 hover:text-charcoal hover:bg-cream hover:border-charcoal/20 focus:outline-none focus:text-charcoal focus:bg-cream focus:border-charcoal/20 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
