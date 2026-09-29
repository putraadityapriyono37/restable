<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-semibold text-charcoal leading-tight">
                {{ __('Closing Kasir') }}
            </h2>
            <form method="GET" action="{{ route('admin.reconciliation') }}" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}"
                    class="border-charcoal/20 focus:border-terracotta focus:ring-terracotta rounded-md shadow-sm text-sm">
                <button type="submit"
                    class="bg-cream hover:bg-cream-dark text-charcoal/80 text-sm px-3 py-2 rounded-md">Filter</button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-olive/15 border border-olive/30 text-olive px-4 py-3 rounded-md text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white border border-charcoal/10 rounded-md p-6">
                @if ($errors->any())
                    <div class="mb-4 bg-brick/10 border border-brick/30 text-brick px-4 py-3 rounded-md text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-6 bg-cream rounded-md p-4">
                    <div class="text-sm text-charcoal/60">Total cash menurut sistem ({{ $date }})</div>
                    <div class="text-2xl font-semibold text-charcoal">
                        Rp {{ number_format($totalCashSystem, 0, ',', '.') }}
                    </div>
                </div>

                @if ($existing && $existing->confirmed_by)
                    <div class="bg-olive/15 border border-olive/30 text-olive px-4 py-3 rounded-md text-sm mb-4">
                        Sudah dikonfirmasi oleh {{ $existing->confirmedBy?->name ?? 'admin' }}.
                        Selisih: Rp {{ number_format((float) $existing->selisih, 0, ',', '.') }}.
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.reconciliation.store') }}" class="space-y-5">
                        @csrf

                        <input type="hidden" name="date" value="{{ $date }}">

                        <div>
                            <x-input-label for="staff_id" value="Staff pencatat" />
                            <select id="staff_id" name="staff_id"
                                class="mt-1 block w-full border-charcoal/20 focus:border-terracotta focus:ring-terracotta rounded-md shadow-sm">
                                @foreach (\App\Models\User::whereHas('roles', fn ($q) => $q->whereIn('name', ['staff', 'admin']))->get() as $user)
                                    <option value="{{ $user->id }}" @selected((int) old('staff_id') === $user->id)>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('staff_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="total_cash_physical" value="Total cash fisik (Rp)" />
                            <x-text-input id="total_cash_physical" name="total_cash_physical" type="number" min="0"
                                class="mt-1 block w-full" :value="old('total_cash_physical', $totalCashSystem)" />
                            <x-input-error :messages="$errors->get('total_cash_physical')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="note" value="Catatan (wajib jika ada selisih)" />
                            <textarea id="note" name="note" rows="3"
                                class="mt-1 block w-full border-charcoal/20 focus:border-terracotta focus:ring-terracotta rounded-md shadow-sm">{{ old('note') }}</textarea>
                            <x-input-error :messages="$errors->get('note')" class="mt-2" />
                        </div>

                        <x-primary-button>Simpan & Konfirmasi Closing</x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
