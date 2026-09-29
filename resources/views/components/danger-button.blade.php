<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-brick border border-transparent rounded-md font-semibold text-sm text-white hover:bg-brick/90 focus:outline-none focus:ring-2 focus:ring-brick focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
