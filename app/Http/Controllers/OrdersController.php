<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function show(
        Request $request,
        string $confirmationNumber
    ) {
        $order = Order::findByConfirmationNumber($confirmationNumber);

        return view('orders.show', ['order' => $order]);
    }
}
