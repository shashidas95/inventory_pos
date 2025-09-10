<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    // Show customers dashboard page
    public function index()
    {
        return view('pages.dashboard.customers.index');
    }

    /**
     * Return all customers (users with role = customer)
     * - Manager: only customers from his store
     * - Admin: all customers
     */
    public function list(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'manager') {
            // Manager only sees customers of his store
            $customers = User::where('role', 'customer')
                ->where('store_id', $user->store_id)
                ->orderBy('name', 'asc')
                ->get();
        } else {
            // Admin (super admin) sees all customers
            $customers = User::where('role', 'customer')
                ->orderBy('name', 'asc')
                ->get();
        }

        if ($customers->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => $customers,
                'message' => 'Customers found.'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'data' => [],
            'message' => 'No customers found.'
        ]);
    }

    /**
     * Show create customer form (Blade)
     */
    public function create()
    {
        return view('pages.dashboard.customers.create');
    }

    /**
     * Store a new customer
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'store_id' => 'nullable|exists:stores,id', // optional, but we’ll control below
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // If manager → force store_id = his own store
            if ($user->role === 'manager') {
                $storeId = $user->store_id;
            } else {
                // Admin can assign store_id from request or leave null
                $storeId = $request->store_id;
            }

            $customer = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'customer',
                'store_id'  => $storeId,
            ]);

            return response()->json([
                'status' => 'success',
                'data'   => $customer,
                'message' => 'Customer created successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create customer: ' . $e->getMessage()
            ], 500);
        }
    }
}
