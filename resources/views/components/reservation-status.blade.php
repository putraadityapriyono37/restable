@props(['status'])

@php
$styles = [
    'pending' => 'bg-cream-dark text-charcoal/60',
    'waiting_payment' => 'bg-ochre/15 text-ochre',
    'confirmed' => 'bg-olive/15 text-olive',
    'cancelled' => 'bg-brick/15 text-brick',
    'completed' => 'bg-charcoal/10 text-charcoal/70',
][$status] ?? 'bg-charcoal/10 text-charcoal/70';

$labels = [
    'pending' => 'Pending',
    'waiting_payment' => 'Menunggu Pembayaran',
    'confirmed' => 'Terkonfirmasi',
    'cancelled' => 'Dibatalkan',
    'completed' => 'Selesai',
][$status] ?? $status;
@endphp

<span class="inline-block px-2.5 py-1 rounded-md text-xs font-medium {{ $styles }}">
    {{ $labels }}
</span>
