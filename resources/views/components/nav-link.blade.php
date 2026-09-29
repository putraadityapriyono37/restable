@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-terracotta text-sm font-medium leading-5 text-charcoal focus:outline-none focus:border-terracotta-dark transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-charcoal/60 hover:text-charcoal hover:border-charcoal/20 focus:outline-none focus:text-charcoal focus:border-charcoal/20 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
