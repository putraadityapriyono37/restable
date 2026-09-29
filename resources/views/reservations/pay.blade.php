<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran DP - Reservasi #{{ $reservation->id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-cream min-h-screen">
    <div class="max-w-xl mx-auto mt-12 bg-white border border-charcoal/10 rounded-md p-8">
        <h1 class="font-serif text-2xl font-semibold text-charcoal mb-4">Konfirmasi Pembayaran DP</h1>

        <dl class="space-y-2 text-sm text-charcoal/80 mb-6">
            <div class="flex justify-between"><dt class="text-charcoal/60">Restoran</dt>
                <dd>{{ $reservation->restaurant->name }}</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal/60">Tanggal</dt>
                <dd>{{ $reservation->reservation_date->format('d M Y') }}</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal/60">Jam</dt>
                <dd>{{ substr($reservation->reservation_time, 0, 5) }}</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal/60">Tamu</dt>
                <dd>{{ $reservation->guest_count }} orang</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal/60">Estimasi total</dt>
                <dd>Rp {{ number_format((float) $reservation->estimated_total, 0, ',', '.') }}</dd></div>
        </dl>

        <button id="pay-button"
            class="w-full bg-terracotta hover:bg-terracotta-dark text-white font-medium py-2.5 px-4 rounded-md">
            Bayar DP Sekarang
        </button>

        <a href="{{ route('reservations.show', $reservation) }}"
            class="block text-center text-sm text-charcoal/60 hover:text-charcoal mt-4">
            Kembali ke detail reservasi
        </a>
    </div>

    <script src="{{ config('services.midtrans.is_production')
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('services.midtrans.client_key') }}"></script>

    <script>
        document.getElementById('pay-button').onclick = function () {
            snap.pay('{{ $snapToken }}', {
                onSuccess: function () {
                    window.location = '{{ route('reservations.show', $reservation) }}';
                },
                onPending: function () {
                    window.location = '{{ route('reservations.show', $reservation) }}';
                },
                onError: function () {
                    window.location = '{{ route('reservations.show', $reservation) }}';
                },
                onClose: function () {
                    window.location = '{{ route('reservations.show', $reservation) }}';
                }
            });
        };
    </script>
</body>

</html>
