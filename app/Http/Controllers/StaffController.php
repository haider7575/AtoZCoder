<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of staff members.
     */
    public function index()
    {
        // Check if admin
        Gate::authorize('manage-products'); // Re-using admin gate or define a new one 'manage-staff'. 
        // Requirement said: "add staff members" (Admin only).
        // I'll check user role directly or reuse 'manage-products' which is admin only.
        // Better to be explicit if I had a gate, but for now 'manage-products' effectively means 'admin'.

        return User::where('role', 'staff')->get();
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        Gate::authorize('manage-products'); // Admin only

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'staff',
        ]);

        if ($request->wantsJson()) {
            return response()->json($user, 201);
        }
        return redirect()->back()->with('success', 'Staff Member Added');
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, User $user)
    {
        Gate::authorize('manage-products'); // Admin only

        if ($user->role !== 'staff') {
            if ($request->wantsJson()) return response()->json(['message' => 'Cannot update non-staff users via this API'], 403);
            return redirect()->back()->with('error', 'Cannot update non-staff users');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($request->wantsJson()) {
            return response()->json($user);
        }
        return redirect()->back()->with('success', 'Staff Member Updating');
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(Request $request, User $user)
    {
        Gate::authorize('manage-products'); // Admin only

        if ($user->role !== 'staff') {
            if ($request->wantsJson()) return response()->json(['message' => 'Cannot delete non-staff users via this API'], 403);
            return redirect()->back()->with('error', 'Cannot delete non-staff users');
        }

        $user->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Staff member deleted']);
        }
        return redirect()->back()->with('success', 'Staff Member Deleted');
    }
}
