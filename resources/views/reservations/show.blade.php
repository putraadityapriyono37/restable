<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-semibold text-charcoal leading-tight">
            Detail Reservasi #{{ $reservation->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-olive/15 border border-olive/30 text-olive px-4 py-3 rounded-md text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white border border-charcoal/10 rounded-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-serif text-lg text-charcoal">Informasi Reservasi</h3>
                    <x-reservation-status :status="$reservation->status" />
                </div>

                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-charcoal/60">Restoran</dt>
                        <dd class="text-charcoal">{{ $reservation->restaurant->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-charcoal/60">Meja</dt>
                        <dd class="text-charcoal">#{{ $reservation->table?->table_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-charcoal/60">Tanggal</dt>
                        <dd class="text-charcoal">
                            {{ \Illuminate\Support\Carbon::parse($reservation->reservation_date)->format('d M Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-charcoal/60">Jam</dt>
                        <dd class="text-charcoal">{{ substr($reservation->reservation_time, 0, 5) }}</dd>
                    </div>
                    <div>
                        <dt class="text-charcoal/60">Jumlah tamu</dt>
                        <dd class="text-charcoal">{{ $reservation->guest_count }} orang</dd>
                    </div>
                    <div>
                        <dt class="text-charcoal/60">Estimasi total</dt>
                        <dd class="text-charcoal">Rp
                            {{ number_format((float) $reservation->estimated_total, 0, ',', '.') }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-charcoal/60">Catatan</dt>
                        <dd class="text-charcoal">{{ $reservation->notes ?? '-' }}</dd>
                    </div>
                </dl>

                {{-- Tombol Bayar DP --}}
                @if ($reservation->status === 'waiting_payment' && $dpTransaction && $dpTransaction->status !== 'settlement')
                    <div class="mt-6">
                        <a href="{{ route('reservations.pay', $reservation) }}"
                            class="inline-block bg-terracotta hover:bg-terracotta-dark text-white text-sm font-medium px-4 py-2 rounded-md">
                            Bayar DP (Rp {{ number_format((float) $dpTransaction->amount, 0, ',', '.') }})
                        </a>
                    </div>
                @endif

                {{-- Tombol Bayar Pelunasan (via sistem) --}}
                @php($pelunasan = $reservation->pelunasanTransaction)
                @if (
                    $reservation->status === 'confirmed' &&
                        $pelunasan &&
                        $pelunasan->payment_method === 'midtrans' &&
                        $pelunasan->status === 'pending')
                    <div class="mt-6">
                        <a href="{{ route('reservations.pay-pelunasan', ['reservation' => $reservation, 'transaction' => $pelunasan]) }}"
                            class="inline-block bg-terracotta hover:bg-terracotta-dark text-white text-sm font-medium px-4 py-2 rounded-md">
                            Bayar Pelunasan (Rp {{ number_format((float) $pelunasan->amount, 0, ',', '.') }})
                        </a>
                    </div>
                @endif
            </div>

            <div class="bg-white border border-charcoal/10 rounded-md p-6">
                <h3 class="font-serif text-lg text-charcoal mb-4">Transaksi</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-charcoal/10 text-sm">
                        <thead>
                            <tr class="text-left text-charcoal/60">
                                <th class="py-2 pr-4">Tipe</th>
                                <th class="py-2 pr-4">Metode</th>
                                <th class="py-2 pr-4">Nominal</th>
                                <th class="py-2 pr-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-charcoal/10">
                            @forelse ($reservation->transactions as $transaction)
                                <tr>
                                    <td class="py-2 pr-4 text-charcoal/80">{{ $transaction->type }}</td>
                                    <td class="py-2 pr-4 text-charcoal/80">{{ $transaction->payment_method }}</td>
                                    <td class="py-2 pr-4 text-charcoal/80">
                                        Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}</td>
                                    <td class="py-2 pr-4 text-charcoal/80">{{ $transaction->status }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-charcoal/60">Belum ada transaksi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-charcoal/10 rounded-md p-6">
                <h3 class="font-serif text-lg text-charcoal mb-4">Riwayat Status</h3>
                <ul class="space-y-3 text-sm">
                    @forelse ($reservation->logs as $log)
                        <li class="border-l-2 border-terracotta/30 pl-3">
                            <div class="text-charcoal">
                                {{ $log->status_from ?? '-' }} → {{ $log->status_to }}
                            </div>
                            <div class="text-charcoal/60 text-xs">
                                {{ $log->created_at->format('d M Y H:i') }}
                                @if ($log->changedBy)
                                    · {{ $log->changedBy->name }}
                                @endif
                            </div>
                            @if ($log->note)
                                <div class="text-charcoal/70 mt-1">{{ $log->note }}</div>
                            @endif
                        </li>
                    @empty
                        <li class="text-charcoal/60">Belum ada log.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
