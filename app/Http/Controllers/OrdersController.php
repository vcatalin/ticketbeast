<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function show(
        Request $request,
        string $confirmationNumber
    ) {
        $order = null;
        return view('orders.show', ['order' => $order]);
    }
}
