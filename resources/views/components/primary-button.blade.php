<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-terracotta border border-transparent rounded-md font-semibold text-sm text-white hover:bg-terracotta-dark focus:outline-none focus:ring-2 focus:ring-terracotta focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
