<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-cream text-charcoal">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-charcoal/10">
            <div class="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
                <a href="/"><x-application-logo class="font-serif text-2xl font-bold" /></a>

                <nav class="flex items-center gap-6">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-charcoal/70 hover:text-charcoal">Beranda</a>
                            <a href="{{ route('reservations.create') }}" class="text-sm font-medium text-charcoal/70 hover:text-charcoal">Buat Reservasi</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-charcoal/70 hover:text-charcoal">Masuk</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-terracotta hover:bg-terracotta-dark text-white text-sm font-medium rounded-md">Daftar</a>
                            @endif
                        @endauth
                    @endif
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <section class="max-w-6xl mx-auto px-6 py-20 lg:py-28 grid gap-12 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="text-sm font-medium uppercase tracking-widest text-terracotta">Reservasi Rumah Makan</p>
                    <h1 class="mt-4 font-serif text-4xl lg:text-5xl font-bold leading-tight">
                        Pesan meja lebih mudah,<br class="hidden sm:block">
                        <span class="text-terracotta">tanpa takut no-show.</span>
                    </h1>
                    <p class="mt-6 text-lg text-charcoal/70 leading-relaxed max-w-xl">
                        ResTable membantu restoran mengelola reservasi meja dengan DP, sehingga pelanggan datang tepat waktu dan operasional harian lebih tenang.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        @auth
                            <a href="{{ route('reservations.create') }}" class="inline-flex items-center px-6 py-3 bg-terracotta hover:bg-terracotta-dark text-white font-medium rounded-md">Buat Reservasi</a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-terracotta hover:bg-terracotta-dark text-white font-medium rounded-md">Mulai Sekarang</a>
                            <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 border border-charcoal/20 hover:border-charcoal/40 text-charcoal font-medium rounded-md">Masuk</a>
                        @endauth
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        ['title' => 'Pilih meja & jam', 'desc' => 'Lihat ketersediaan meja dan pilih slot yang paling pas.'],
                        ['title' => 'Bayar DP', 'desc' => 'Amankan reservasi dengan down payment via Midtrans.'],
                        ['title' => 'Terkonfirmasi otomatis', 'desc' => 'DP terbayar, reservasi langsung terkonfirmasi.'],
                        ['title' => 'Pelunasan fleksibel', 'desc' => 'Bayar sisa tagihan via sistem atau cash di lokasi.'],
                    ] as $feature)
                        <div class="bg-white border border-charcoal/10 rounded-md p-6">
                            <div class="font-serif text-lg text-charcoal">{{ $feature['title'] }}</div>
                            <div class="mt-2 text-sm text-charcoal/60">{{ $feature['desc'] }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        </main>

        <footer class="border-t border-charcoal/10">
            <div class="max-w-6xl mx-auto px-6 py-6 text-sm text-charcoal/60">
                &copy; {{ date('Y') }} ResTable. Sistem reservasi rumah makan.
            </div>
        </footer>
    </div>
</body>
</html>
