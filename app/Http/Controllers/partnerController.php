<?php

namespace App\Http\Controllers;

use App\Models\Partners;
use Illuminate\Http\Request;

class partnerController extends Controller
{
    public function index(Request $request)
    {
        $partners = Partners::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partners.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'logo_url' => 'required|url|max:255',
        ]);

        Partners::create($data);

        return redirect()->route('partners.index')->with('success', 'Partner berhasil ditambahkan.');
    }

    public function edit(Partners $partner)
    {
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, Partners $partner)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'logo_url' => 'required|url|max:255',
        ]);

        $partner->update($data);

        return redirect()->route('partners.index')->with('success', 'Partner berhasil diperbarui.');
    }

    public function destroy(Partners $partner)
    {
        $partner->delete();
        return redirect()->route('partners.index')->with('success', 'Partner berhasil dihapus.');
    }
}
