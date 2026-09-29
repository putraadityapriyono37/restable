<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-white border border-charcoal/20 rounded-md font-semibold text-sm text-charcoal hover:bg-cream focus:outline-none focus:ring-2 focus:ring-terracotta focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
