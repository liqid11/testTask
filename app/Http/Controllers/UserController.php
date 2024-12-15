<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|in:asc,desc',
        ]);

        $query = User::query();

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        // Sort by name
        if ($request->filled('sort')) {
            $query->orderBy('name', $request->input('sort'));
        }

        return response()->json($query->paginate(10));
    }

    public function show(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:20',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|max:20',
            'ip' => 'nullable|ip',
            'comment' => 'nullable|string|max:255',
        ]);

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'string|max:20',
            'password' => 'string|min:8|max:20',
            'ip' => 'nullable|ip',
            'comment' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
