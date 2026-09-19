<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users    = User::with('manager')->orderBy('role')->orderBy('name')->get();
        $managers = User::whereIn('role', ['direktur', 'manajer'])->orderBy('name')->get();
        return view('users.index', compact('users', 'managers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:8|confirmed',
            'role'       => 'required|in:direktur,manajer,staff',
            'phone'      => 'nullable|string|max:20',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'phone'      => $request->phone,
            'manager_id' => $request->manager_id,
            'is_active'  => true,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,'.$user->id,
            'role'       => 'required|in:direktur,manajer,staff',
            'phone'      => 'nullable|string|max:20',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $data = [
            'name'       => $request->name,
            'email'      => $request->email,
            'role'       => $request->role,
            'phone'      => $request->phone,
            'manager_id' => $request->manager_id,
            'is_active'  => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak bisa hapus akun sendiri!');
        }

        // JANGAN hard delete: leads.assigned_to menunjuk ke user ini.
        // Menghapusnya membuat lead yatim / error foreign key.
        // Nonaktifkan saja — user langsung tidak bisa login (lihat LoginRequest).
        $user->update(['is_active' => false]);
        $user->tokens()->delete();   // cabut token aplikasi HP

        return redirect()->route('users.index')
            ->with('success', "Akun \"{$user->name}\" dinonaktifkan. Data lead-nya tetap utuh.");
    }
}