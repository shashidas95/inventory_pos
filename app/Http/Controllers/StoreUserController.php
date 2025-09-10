<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;

class StoreUserController extends Controller
{
    /**
     * Show all stores with their users.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $stores = Store::with('users')->get();
        } elseif ($user->role === 'manager') {
            // Managers only see their own store
            $stores = Store::with('users')->where('id', $user->store_id)->get();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view stores.'
            ], 403);
        }

        $users = User::all(); // all system users
        return view('components.dashboard.admin.stores.index', compact('stores', 'users'));
    }

    /**
     * Assign user to store with role.
     */
    public function assign(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'user_id'  => 'required|exists:users,id',
            'role'     => 'required|in:manager,staff',
        ]);

        // Managers can only assign users to their own store
        if ($user->role === 'manager') {
            if ($request->store_id != $user->store_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to assign users to this store.'
                ], 403);
            }

            // Managers cannot assign another manager
            if ($request->role === 'manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'Managers cannot assign another manager.'
                ], 403);
            }
        }

        $store = Store::findOrFail($request->store_id);

        // attach user with role in pivot
        $store->users()->syncWithoutDetaching([
            $request->user_id => ['role' => $request->role]
        ]);

        $assignedUser = User::find($request->user_id);

        return response()->json([
            'success' => true,
            'user' => $assignedUser,
            'role' => $request->role,
            'availableUsers' => $this->getAvailableUsersForAllStores($user),
        ]);
    }

    /**
     * Remove user from store.
     */
    /**
     * Remove user from store.
     */
    public function remove(Store $store, User $user)
    {
        $authUser = auth()->user();

        // Managers can only remove users from their own store
        if ($authUser->role === 'manager') {
            if ($store->id != $authUser->store_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to remove users from this store.'
                ], 403);
            }

            // Managers cannot remove another manager
            $pivotRole = $store->users()->where('user_id', $user->id)->first()?->pivot->role;
            if ($pivotRole === 'manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'Managers cannot remove another manager.'
                ], 403);
            }
        }

        $store->users()->detach($user->id);

        return response()->json([
            'success' => true,
            'availableUsers' => $this->getAvailableUsersForAllStores($authUser),
        ]);
    }

    /**
     * Helper: get available users per store
     */
    private function getAvailableUsersForAllStores($authUser)
    {
        $storesQuery = Store::with('users');
        if ($authUser->role === 'manager') {
            $storesQuery->where('id', $authUser->store_id);
        }

        $stores = $storesQuery->get();
        $allUsers = User::all();

        $result = [];
        foreach ($stores as $store) {
            $result[$store->id] = $allUsers->reject(function ($u) use ($store) {
                return $store->users->contains($u);
            })->map(function ($u) {
                return ['id' => $u->id, 'name' => $u->name];
            })->values();
        }

        return $result;
    }
}
