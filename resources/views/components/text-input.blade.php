@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-charcoal/20 focus:border-terracotta focus:ring-terracotta rounded-md']) }}>
