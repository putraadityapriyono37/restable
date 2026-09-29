<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-semibold text-charcoal leading-tight">
            {{ __('Pengaturan Restoran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-charcoal/10 rounded-md p-6">
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

                <form method="POST" action="{{ route('admin.restaurant-settings.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="Nama restoran" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full"
                            :value="old('name', $restaurant->name)" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="address" value="Alamat" />
                        <x-text-input id="address" name="address" class="mt-1 block w-full"
                            :value="old('address', $restaurant->address)" />
                    </div>

                    <div>
                        <x-input-label for="phone" value="Telepon" />
                        <x-text-input id="phone" name="phone" class="mt-1 block w-full"
                            :value="old('phone', $restaurant->phone)" />
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="open_time" value="Jam buka" />
                            <x-text-input id="open_time" name="open_time" type="time" class="mt-1 block w-full"
                                :value="old('open_time', substr($restaurant->open_time, 0, 5))" />
                            <x-input-error :messages="$errors->get('open_time')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="close_time" value="Jam tutup" />
                            <x-text-input id="close_time" name="close_time" type="time" class="mt-1 block w-full"
                                :value="old('close_time', substr($restaurant->close_time, 0, 5))" />
                            <x-input-error :messages="$errors->get('close_time')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="slot_duration" value="Durasi slot (menit)" />
                        <x-text-input id="slot_duration" name="slot_duration" type="number" min="15" max="480"
                            class="mt-1 block w-full" :value="old('slot_duration', $restaurant->slot_duration)" />
                        <x-input-error :messages="$errors->get('slot_duration')" class="mt-2" />
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="dp_type" value="Tipe DP" />
                            <select id="dp_type" name="dp_type"
                                class="mt-1 block w-full border-charcoal/20 focus:border-terracotta focus:ring-terracotta rounded-md shadow-sm">
                                <option value="percentage" @selected(old('dp_type', $restaurant->dp_type) === 'percentage')>
                                    Persentase (%)</option>
                                <option value="fixed" @selected(old('dp_type', $restaurant->dp_type) === 'fixed')>
                                    Nominal tetap (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="dp_value" value="Nilai DP" />
                            <x-text-input id="dp_value" name="dp_value" type="number" min="0" step="0.01"
                                class="mt-1 block w-full" :value="old('dp_value', $restaurant->dp_value)" />
                            <x-input-error :messages="$errors->get('dp_value')" class="mt-2" />
                        </div>
                    </div>

                    <x-primary-button>Simpan Pengaturan</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
