<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-semibold text-charcoal leading-tight">
            {{ __('Buat Reservasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-charcoal/10 rounded-md">
                <div class="p-6">
                    @if ($errors->any())
                        <div class="mb-4 bg-brick/10 border border-brick/30 text-brick px-4 py-3 rounded-md text-sm">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('reservations.store') }}" class="space-y-5">
                        @csrf

                        <div>
                            <x-input-label for="restaurant_id" value="Restoran" />
                            <select id="restaurant_id" name="restaurant_id"
                                class="mt-1 block w-full border-charcoal/20 focus:border-terracotta focus:ring-terracotta rounded-md"
                                onchange="window.location='{{ route('reservations.create') }}?restaurant_id=' + this.value">
                                @foreach ($restaurants as $restaurant)
                                    <option value="{{ $restaurant->id }}"
                                        @selected($selectedRestaurant && $selectedRestaurant->id === $restaurant->id)>
                                        {{ $restaurant->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('restaurant_id')" class="mt-2" />
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-input-label for="reservation_date" value="Tanggal" />
                                <x-text-input id="reservation_date" name="reservation_date" type="date"
                                    class="mt-1 block w-full"
                                    :value="old('reservation_date', now()->addDay()->toDateString())"
                                    :min="now()->toDateString()" />
                                <x-input-error :messages="$errors->get('reservation_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="reservation_time" value="Jam" />
                                <select id="reservation_time" name="reservation_time"
                                    class="mt-1 block w-full border-charcoal/20 focus:border-terracotta focus:ring-terracotta rounded-md">
                                    @foreach ($slots as $slot)
                                        <option value="{{ $slot }}" @selected(old('reservation_time') === $slot)>
                                            {{ $slot }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('reservation_time')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-input-label for="guest_count" value="Jumlah tamu" />
                                <x-text-input id="guest_count" name="guest_count" type="number" min="1" max="50"
                                    class="mt-1 block w-full" :value="old('guest_count', 2)" />
                                <x-input-error :messages="$errors->get('guest_count')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="estimated_total" value="Estimasi total belanja (Rp)" />
                                <x-text-input id="estimated_total" name="estimated_total" type="number" min="1000"
                                    class="mt-1 block w-full" :value="old('estimated_total', 200000)" />
                                <p class="mt-1 text-xs text-charcoal/60">DP dihitung dari estimasi ini.</p>
                                <x-input-error :messages="$errors->get('estimated_total')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="notes" value="Catatan (opsional)" />
                            <textarea id="notes" name="notes" rows="3"
                                class="mt-1 block w-full border-charcoal/20 focus:border-terracotta focus:ring-terracotta rounded-md">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        @if ($selectedRestaurant)
                            <p class="text-sm text-charcoal/60">
                                Jam operasional: {{ substr($selectedRestaurant->open_time, 0, 5) }} -
                                {{ substr($selectedRestaurant->close_time, 0, 5) }}.
                                Slot {{ $selectedRestaurant->slot_duration }} menit.
                                DP {{ $selectedRestaurant->dp_type === 'percentage'
                                    ? $selectedRestaurant->dp_value . '%'
                                    : 'Rp ' . number_format((float) $selectedRestaurant->dp_value, 0, ',', '.') }}.
                            </p>
                        @endif

                        <x-primary-button>Reservasi & Bayar DP</x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
