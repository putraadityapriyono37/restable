<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\TableModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(Request $request): View
    {
        $restaurant = $this->restaurant($request);

        $tables = $restaurant->tables()->orderBy('table_number')->paginate(15);

        return view('tables.index', compact('restaurant', 'tables'));
    }

    public function create(Request $request): View
    {
        $restaurant = $this->restaurant($request);

        return view('tables.create', compact('restaurant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $restaurant = $this->restaurant($request);

        $validated = $request->validate([
            'table_number' => 'required|string|max:20|unique:tables,table_number,'.$restaurant->id.',restaurant_id',
            'capacity' => 'required|integer|min:1|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $restaurant->tables()->create([
            'table_number' => $validated['table_number'],
            'capacity' => $validated['capacity'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.tables.index')
            ->with('status', 'Meja berhasil ditambahkan.');
    }

    public function edit(Request $request, TableModel $table): View
    {
        $restaurant = $this->restaurant($request);
        abort_unless($table->restaurant_id === $restaurant->id, 404);

        return view('tables.edit', compact('restaurant', 'table'));
    }

    public function update(Request $request, TableModel $table): RedirectResponse
    {
        $restaurant = $this->restaurant($request);
        abort_unless($table->restaurant_id === $restaurant->id, 404);

        $validated = $request->validate([
            'table_number' => 'required|string|max:20|unique:tables,table_number,'.$table->id.',id',
            'capacity' => 'required|integer|min:1|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $table->update([
            'table_number' => $validated['table_number'],
            'capacity' => $validated['capacity'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.tables.index')
            ->with('status', 'Meja berhasil diperbarui.');
    }

    public function destroy(Request $request, TableModel $table): RedirectResponse
    {
        $restaurant = $this->restaurant($request);
        abort_unless($table->restaurant_id === $restaurant->id, 404);

        $table->delete();

        return redirect()
            ->route('admin.tables.index')
            ->with('status', 'Meja berhasil dihapus.');
    }

    private function restaurant(Request $request): Restaurant
    {
        return Restaurant::where('owner_id', $request->user()->id)->firstOrFail();
    }
}
