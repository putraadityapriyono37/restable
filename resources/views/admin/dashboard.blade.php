<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-semibold text-charcoal leading-tight">
                {{ __('Dashboard Admin') }}
            </h2>
            <a href="{{ route('admin.restaurant-settings.edit') }}"
                class="text-sm text-terracotta hover:text-terracotta-dark">Pengaturan Restoran</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach (['pending', 'waiting_payment', 'confirmed', 'cancelled', 'completed'] as $status)
                    <div class="bg-white border border-charcoal/10 rounded-md p-5">
                        <div class="text-sm text-charcoal/60">{{ $status }}</div>
                        <div class="text-2xl font-semibold text-charcoal mt-1">
                            {{ $statusCounts[$status] ?? 0 }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white border border-charcoal/10 rounded-md p-6">
                <h3 class="font-semibold text-charcoal mb-1">Pendapatan Hari Ini</h3>
                <p class="text-3xl font-semibold text-charcoal">
                    Rp {{ number_format($revenueToday, 0, ',', '.') }}
                </p>
                <p class="text-xs text-charcoal/60 mt-1">Total transaksi settlement hari ini.</p>
            </div>

            <div class="bg-white border border-charcoal/10 rounded-md p-6">
                <h3 class="font-semibold text-charcoal mb-4">Breakdown per Metode (Hari Ini)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-charcoal/10 text-sm">
                        <thead>
                            <tr class="text-left text-charcoal/60">
                                <th class="py-2 pr-4">Metode</th>
                                <th class="py-2 pr-4">Tipe</th>
                                <th class="py-2 pr-4">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-charcoal/10">
                            @forelse ($revenueByMethod as $row)
                                <tr>
                                    <td class="py-2 pr-4 text-charcoal/80">{{ $row->payment_method }}</td>
                                    <td class="py-2 pr-4 text-charcoal/80">{{ $row->type }}</td>
                                    <td class="py-2 pr-4 text-charcoal/80">
                                        Rp {{ number_format((float) $row->total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-charcoal/60">Belum ada transaksi hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.tables.index') }}"
                    class="bg-white border border-charcoal/20 text-charcoal/80 text-sm px-4 py-2 rounded-md hover:bg-cream">
                    Kelola Meja
                </a>
                <a href="{{ route('admin.reconciliation') }}"
                    class="bg-white border border-charcoal/20 text-charcoal/80 text-sm px-4 py-2 rounded-md hover:bg-cream">
                    Closing Kasir
                </a>
                <a href="{{ route('staff.reservations') }}"
                    class="bg-white border border-charcoal/20 text-charcoal/80 text-sm px-4 py-2 rounded-md hover:bg-cream">
                    Reservasi Aktif
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
