<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use Illuminate\Http\Request;

class CreditPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $packages = CreditPackage::ordered()->get();

        return view('admin.credit-packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.credit-packages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'credit_amount' => ['required', 'integer', 'min:1'],
            'bonus_credits' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        CreditPackage::create($request->all());

        return redirect()->route('admin.credit-packages.index')
            ->with('success', 'Paket kredit berhasil dibuat');
    }

    public function edit(CreditPackage $creditPackage)
    {
        return view('admin.credit-packages.edit', compact('creditPackage'));
    }

    public function update(Request $request, CreditPackage $creditPackage)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'credit_amount' => ['required', 'integer', 'min:1'],
            'bonus_credits' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $creditPackage->update($request->all());

        return redirect()->route('admin.credit-packages.index')
            ->with('success', 'Paket kredit berhasil diupdate');
    }

    public function destroy(CreditPackage $creditPackage)
    {
        $creditPackage->delete();

        return redirect()->route('admin.credit-packages.index')
            ->with('success', 'Paket kredit berhasil dihapus');
    }

    public function toggleStatus(CreditPackage $creditPackage)
    {
        $creditPackage->update(['is_active' => ! $creditPackage->is_active]);
        $status = $creditPackage->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Paket kredit berhasil {$status}");
    }
}
