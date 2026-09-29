<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-semibold text-charcoal leading-tight">
                {{ __('Riwayat Reservasi') }}
            </h2>
            <a href="{{ route('reservations.create') }}"
                class="bg-terracotta hover:bg-terracotta-dark text-white text-sm font-medium px-4 py-2 rounded-md">
                Buat Reservasi
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-olive/15 border border-olive/30 text-olive px-4 py-3 rounded-md text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white border border-charcoal/10 rounded-md">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-charcoal/10 text-sm">
                        <thead class="bg-cream">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-charcoal/60">#</th>
                                <th class="px-6 py-3 text-left font-medium text-charcoal/60">Restoran</th>
                                <th class="px-6 py-3 text-left font-medium text-charcoal/60">Tanggal</th>
                                <th class="px-6 py-3 text-left font-medium text-charcoal/60">Jam</th>
                                <th class="px-6 py-3 text-left font-medium text-charcoal/60">Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-charcoal/10">
                            @forelse ($reservations as $reservation)
                                <tr>
                                    <td class="px-6 py-4 text-charcoal">{{ $reservation->id }}</td>
                                    <td class="px-6 py-4 text-charcoal/80">{{ $reservation->restaurant->name }}</td>
                                    <td class="px-6 py-4 text-charcoal/80">
                                        {{ \Illuminate\Support\Carbon::parse($reservation->reservation_date)->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-charcoal/80">
                                        {{ substr($reservation->reservation_time, 0, 5) }}</td>
                                    <td class="px-6 py-4">
                                        <x-reservation-status :status="$reservation->status" />
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a class="text-terracotta hover:text-terracotta-dark font-medium"
                                            href="{{ route('reservations.show', $reservation) }}">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-charcoal/60">
                                        Belum ada reservasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $reservations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
