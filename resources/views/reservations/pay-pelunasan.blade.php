<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pelunasan - Reservasi #{{ $reservation->id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-cream min-h-screen">
    <div class="max-w-xl mx-auto mt-12 bg-white border border-charcoal/10 rounded-md p-8">
        <h1 class="font-serif text-2xl font-semibold text-charcoal mb-4">Pembayaran Pelunasan</h1>

        <dl class="space-y-2 text-sm text-charcoal/80 mb-6">
            <div class="flex justify-between"><dt class="text-charcoal/60">Reservasi</dt>
                <dd>#{{ $reservation->id }}</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal/60">Restoran</dt>
                <dd>{{ $reservation->restaurant->name }}</dd></div>
            <div class="flex justify-between"><dt class="text-charcoal/60">Nominal</dt>
                <dd>Rp {{ number_format((float) $transaction->amount, 0, ',', '.') }}</dd></div>
        </dl>

        <button id="pay-button"
            class="w-full bg-terracotta hover:bg-terracotta-dark text-white font-medium py-2.5 px-4 rounded-md">
            Bayar Sekarang
        </button>
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
