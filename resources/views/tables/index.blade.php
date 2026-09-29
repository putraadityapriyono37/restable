<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-semibold text-charcoal leading-tight">
            {{ __('Kelola Meja') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 bg-olive/15 border border-olive/30 text-olive px-4 py-3 rounded-md text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white border border-charcoal/10 overflow-hidden rounded-md">
                <div class="p-6 flex items-center justify-between">
                    <p class="text-sm text-charcoal/60">Restoran: <strong>{{ $restaurant->name }}</strong></p>
                    <a href="{{ route('admin.tables.create') }}"
                        class="bg-terracotta hover:bg-terracotta-dark text-white text-sm font-medium px-4 py-2 rounded-md">
                        Tambah Meja
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-charcoal/10 text-sm">
                        <thead class="bg-cream">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-charcoal/60">No. Meja</th>
                                <th class="px-6 py-3 text-left font-medium text-charcoal/60">Kapasitas</th>
                                <th class="px-6 py-3 text-left font-medium text-charcoal/60">Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-charcoal/10">
                            @forelse ($tables as $table)
                                <tr>
                                    <td class="px-6 py-4 text-charcoal">{{ $table->table_number }}</td>
                                    <td class="px-6 py-4 text-charcoal/80">{{ $table->capacity }} orang</td>
                                    <td class="px-6 py-4">
                                        @if ($table->is_active)
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-medium bg-olive/15 text-olive">Aktif</span>
                                        @else
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-medium bg-charcoal/10 text-charcoal/70">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3">
                                        <a class="text-terracotta hover:text-terracotta-dark"
                                            href="{{ route('admin.tables.edit', $table) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.tables.destroy', $table) }}"
                                            class="inline"
                                            onsubmit="return confirm('Hapus meja ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-brick hover:text-brick/90">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-charcoal/60">Belum ada meja.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">{{ $tables->links() }}</div>
        </div>
    </div>
</x-app-layout>
