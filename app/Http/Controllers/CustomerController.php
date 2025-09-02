<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index()
    {
        return view('pages.dashboard.customers.index');
    }
    /**
     * Return all customers (users with role = customer)
     */
    public function list()
    {
        $customers = User::where('role', 'customer')->orderBy('name', 'asc')->get();

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
        return view('pages.dashboard.customers.create'); // create.blade.php
    }

    /**
     * Store new customer
     */
    // Store a new customer
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'customer'
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $customer,
                'message' => 'Customer created successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create customer: ' . $e->getMessage()
            ], 500);
        }
    }
}
