<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-semibold text-charcoal leading-tight">
                {{ __('Reservasi Aktif') }}
            </h2>
            <form method="GET" action="{{ route('staff.reservations') }}" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}"
                    class="border-charcoal/20 focus:border-terracotta focus:ring-terracotta rounded-md shadow-sm text-sm">
                <button type="submit"
                    class="bg-cream hover:bg-cream-dark text-charcoal/80 text-sm px-3 py-2 rounded-md">Filter</button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-olive/15 border border-olive/30 text-olive px-4 py-3 rounded-md text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-brick/10 border border-brick/30 text-brick px-4 py-3 rounded-md text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white border border-charcoal/10 overflow-hidden rounded-md">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-charcoal/10 text-sm">
                        <thead class="bg-cream">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-charcoal/60">#</th>
                                <th class="px-4 py-3 text-left font-medium text-charcoal/60">Pelanggan</th>
                                <th class="px-4 py-3 text-left font-medium text-charcoal/60">Jam</th>
                                <th class="px-4 py-3 text-left font-medium text-charcoal/60">Meja</th>
                                <th class="px-4 py-3 text-left font-medium text-charcoal/60">Estimasi</th>
                                <th class="px-4 py-3 text-left font-medium text-charcoal/60">Sisa Tagihan</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-charcoal/10">
                            @forelse ($reservations as $reservation)
                                <tr>
                                    <td class="px-4 py-4 text-charcoal">{{ $reservation->id }}</td>
                                    <td class="px-4 py-4 text-charcoal/80">{{ $reservation->user->name }}</td>
                                    <td class="px-4 py-4 text-charcoal/80">
                                        {{ substr($reservation->reservation_time, 0, 5) }}</td>
                                    <td class="px-4 py-4 text-charcoal/80">
                                        #{{ $reservation->table?->table_number ?? '-' }}</td>
                                    <td class="px-4 py-4 text-charcoal/80">
                                        Rp {{ number_format((float) $reservation->estimated_total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-charcoal/80">
                                        Rp {{ number_format($reservation->remainingBalance(), 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right">
                                        @can('input-pelunasan-cash')
                                            <button type="button"
                                                class="text-terracotta hover:text-terracotta-dark font-medium"
                                                onclick="openPelunasan({{ $reservation->id }}, {{ $reservation->remainingBalance() }}, '{{ $reservation->user->name }}')">
                                                Pelunasan
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-charcoal/60">
                                        Tidak ada reservasi aktif pada tanggal ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">{{ $reservations->links() }}</div>
        </div>
    </div>

    @can('input-pelunasan-cash')
        <div id="pelunasan-modal" class="hidden fixed inset-0 bg-charcoal/70 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-md border border-charcoal/10 max-w-md w-full p-6">
                <h3 class="font-serif text-lg text-charcoal mb-4">Input Pelunasan</h3>

                <form method="POST" action="{{ route('staff.pelunasan') }}" class="space-y-4" id="pelunasan-form">
                    @csrf
                    <input type="hidden" name="reservation_id" id="pelunasan-reservation-id">

                    <div>
                        <label class="block text-sm font-medium text-charcoal/80">Reservasi</label>
                        <p class="text-sm text-charcoal" id="pelunasan-label"></p>
                    </div>

                    <div>
                        <label for="pelunasan-amount"
                            class="block text-sm font-medium text-charcoal/80">Nominal Pelunasan (Rp)</label>
                        <input type="number" name="amount" id="pelunasan-amount" min="1" required
                            class="mt-1 block w-full border-charcoal/20 focus:border-terracotta focus:ring-terracotta rounded-md shadow-sm">
                        <p class="mt-1 text-xs text-charcoal/60">Sisa tagihan:
                            Rp <span id="pelunasan-remaining"></span></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-charcoal/80">Metode</label>
                        <label class="inline-flex items-center me-4 mt-1">
                            <input type="radio" name="payment_method" value="cash" checked
                                class="text-terracotta focus:ring-terracotta"
                                onchange="toggleCashConfirm()">
                            <span class="ms-2 text-sm text-charcoal/80">Cash</span>
                        </label>
                        <label class="inline-flex items-center mt-1">
                            <input type="radio" name="payment_method" value="midtrans"
                                class="text-terracotta focus:ring-terracotta"
                                onchange="toggleCashConfirm()">
                            <span class="ms-2 text-sm text-charcoal/80">Via Sistem (Snap)</span>
                        </label>
                    </div>

                    <div id="cash-confirm-wrap">
                        <label class="inline-flex items-start">
                            <input type="checkbox" name="confirm_cash" value="1"
                                class="mt-1 text-terracotta focus:ring-terracotta rounded">
                            <span class="ms-2 text-sm text-charcoal/80">
                                Konfirmasi uang cash telah diterima dari pelanggan.
                            </span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button"
                            class="px-4 py-2 text-sm text-charcoal/70 hover:text-charcoal"
                            onclick="closePelunasan()">Batal</button>
                        <button type="submit"
                            class="bg-terracotta hover:bg-terracotta-dark text-white text-sm font-medium px-4 py-2 rounded-md">
                            Simpan Pelunasan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openPelunasan(id, remaining, name) {
                document.getElementById('pelunasan-reservation-id').value = id;
                document.getElementById('pelunasan-amount').value = Math.round(remaining);
                document.getElementById('pelunasan-remaining').textContent =
                    Number(remaining).toLocaleString('id-ID');
                document.getElementById('pelunasan-label').textContent = '#' + id + ' — ' + name;
                document.getElementById('pelunasan-modal').classList.remove('hidden');
            }

            function closePelunasan() {
                document.getElementById('pelunasan-modal').classList.add('hidden');
            }

            function toggleCashConfirm() {
                const method = document.querySelector('input[name="payment_method"]:checked').value;
                document.getElementById('cash-confirm-wrap').style.display =
                    method === 'cash' ? 'block' : 'none';
            }
        </script>
    @endcan
</x-app-layout>
