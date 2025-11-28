<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Http\Requests\Staff\StaffCreateRequest;
use App\Http\Requests\Staff\StaffUpdateRequest;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        try {
            $users = User::all()->where('role_id', '!=', Role::where('name', 'admin')->first()->id);

            return response()->json([
                'error' => false,
                'message' => 'Users found successfully',
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::find($id);

            return response()->json([
                'error' => false,
                'message' => 'User found successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(StaffCreateRequest $request)
    {
        try {
            $validated = $request->validated();
            $generatedPassword = fake()->password(8, 12);

            $validated['password'] = Hash::make($generatedPassword);
            $validated['generated_password'] = $generatedPassword;

            $user = User::create($validated);

            return response()->json([
                'error' => false,
                'message' => 'User created successfully',
                'data' => $user,
                'password' => $generatedPassword
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(StaffUpdateRequest $request, $id)
    {
        try {
            $validated = $request->validated();

            $user = User::find($id);
            $user->update($validated);

            return response()->json([
                'error' => false,
                'message' => 'User updated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);
            $user->delete();

            return response()->json([
                'error' => false,
                'message' => 'User deleted successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }
}
