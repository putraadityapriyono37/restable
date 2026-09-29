<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-semibold text-charcoal leading-tight">
            {{ __('Edit Meja #' . $table->table_number) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
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

                <form method="POST" action="{{ route('admin.tables.update', $table) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="table_number" value="Nomor Meja" />
                        <x-text-input id="table_number" name="table_number" class="mt-1 block w-full"
                            :value="old('table_number', $table->table_number)" />
                        <x-input-error :messages="$errors->get('table_number')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="capacity" value="Kapasitas (orang)" />
                        <x-text-input id="capacity" name="capacity" type="number" min="1" max="50"
                            class="mt-1 block w-full" :value="old('capacity', $table->capacity)" />
                        <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
                    </div>

                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1"
                            @checked(old('is_active', $table->is_active))
                            class="rounded border-charcoal/20 text-terracotta focus:ring-terracotta">
                        <span class="ms-2 text-sm text-charcoal/80">Aktif</span>
                    </label>

                    <div class="flex items-center gap-3">
                        <x-primary-button>Simpan</x-primary-button>
                        <a href="{{ route('admin.tables.index') }}" class="text-sm text-charcoal/60 hover:text-charcoal/80">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
