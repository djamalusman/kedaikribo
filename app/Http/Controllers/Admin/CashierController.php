<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CashierController extends Controller
{
    protected function cashierRoleId()
    {
        return Role::where('name', 'kasir')->value('id');
    }

    public function index()
    {
        $cashiers = User::with('outlet')
            ->where('role_id', $this->cashierRoleId())
            ->orderBy('name')
            ->paginate(20);

        return view('admin.cashiers.index', compact('cashiers'));
    }

    public function create()
    {
        $outlets = Outlet::orderBy('name')->get();

        return view('admin.cashiers.create', compact('outlets'));
    }

    public function store(Request $request)
    {
        $roleId = $this->cashierRoleId();

        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        $data['role_id'] = $roleId;
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.cashiers.index')
            ->with('success', 'Kasir berhasil dibuat.');
    }

    public function edit(User $cashier)
    {
        $outlets = Outlet::orderBy('name')->get();

        return view('admin.cashiers.edit', compact('cashier','outlets'));
    }

    public function update(Request $request, User $cashier)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'email'     => 'required|email|unique:users,email,'.$cashier->id,
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        $cashier->update($data);

        return redirect()->route('admin.cashiers.index')
            ->with('success', 'Kasir berhasil diupdate.');
    }

    public function destroy(User $cashier)
    {
        $cashier->delete();

        return redirect()->route('admin.cashiers.index')
            ->with('success', 'Kasir dihapus.');
    }

    // Reset password ke default
    public function resetPassword(User $cashier)
    {
        $cashier->update([
            'password' => Hash::make('password'),
        ]);

        return back()->with('success', 'Password kasir di-reset ke "password".');
    }
}
