<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $today = Invoice::whereDate('invoice_date', Carbon::today())->sum('total_amount');
        $month = Invoice::whereMonth('invoice_date', Carbon::now()->month)->sum('total_amount');
        $total = Invoice::sum('total_amount');

        return response()->json([
            'status' => 'success',
            'today_sales' => $today,
            'month_sales' => $month,
            'total_sales' => $total
        ]);
    }
}
