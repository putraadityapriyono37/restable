<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-semibold text-charcoal leading-tight">
            {{ __('Beranda') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white border border-charcoal/10 rounded-md">
                <div class="p-6">
                    <p class="font-serif text-lg text-charcoal">Halo, {{ Auth::user()->name }}!</p>
                    <p class="text-sm text-charcoal/60 mt-1">Selamat datang di ResTable — kelola reservasi restoran Anda dari sini.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('reservations.create') }}"
                   class="block bg-white border border-charcoal/10 rounded-md p-6 hover:border-terracotta/50 transition">
                    <div class="font-serif text-lg text-charcoal">Buat Reservasi</div>
                    <div class="text-sm text-charcoal/60 mt-1">Pilih meja, bayar DP, langsung konfirmasi.</div>
                </a>

                <a href="{{ route('reservations.index') }}"
                   class="block bg-white border border-charcoal/10 rounded-md p-6 hover:border-terracotta/50 transition">
                    <div class="font-serif text-lg text-charcoal">Riwayat Reservasi</div>
                    <div class="text-sm text-charcoal/60 mt-1">Lihat status dan detail reservasi Anda.</div>
                </a>

                @can('lihat-reservasi-aktif')
                    <a href="{{ route('staff.reservations') }}"
                       class="block bg-white border border-charcoal/10 rounded-md p-6 hover:border-terracotta/50 transition">
                        <div class="font-serif text-lg text-charcoal">Reservasi Aktif (Staff)</div>
                        <div class="text-sm text-charcoal/60 mt-1">Check-in dan input pelunasan.</div>
                    </a>
                @endcan

                @role('admin')
                    <a href="{{ route('admin.dashboard') }}"
                       class="block bg-white border border-charcoal/10 rounded-md p-6 hover:border-terracotta/50 transition">
                        <div class="font-serif text-lg text-charcoal">Dashboard Admin</div>
                        <div class="text-sm text-charcoal/60 mt-1">Statistik reservasi dan pendapatan.</div>
                    </a>
                    <a href="{{ route('admin.tables.index') }}"
                       class="block bg-white border border-charcoal/10 rounded-md p-6 hover:border-terracotta/50 transition">
                        <div class="font-serif text-lg text-charcoal">Kelola Meja</div>
                        <div class="text-sm text-charcoal/60 mt-1">Tambah, ubah, nonaktifkan meja.</div>
                    </a>
                    <a href="{{ route('admin.reconciliation') }}"
                       class="block bg-white border border-charcoal/10 rounded-md p-6 hover:border-terracotta/50 transition">
                        <div class="font-serif text-lg text-charcoal">Closing Kasir</div>
                        <div class="text-sm text-charcoal/60 mt-1">Rekonsiliasi cash harian.</div>
                    </a>
                @endrole
            </div>
        </div>
    </div>
</x-app-layout>
