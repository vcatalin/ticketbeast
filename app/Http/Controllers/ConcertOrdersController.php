<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Models\Concert;
use Illuminate\Http\JsonResponse;

class ConcertOrdersController extends Controller
{
    public function store(
        Request $request,
        int $concertId,
        PaymentGateway $paymentGateway
    ) {
        $concert = Concert::find($concertId);
        $ticketQuantity = $request->input('ticket_quantity');
        $amount = $ticketQuantity * $concert->ticket_price;
        $paymentGateway->charge($concertId, $request->input('payment_token'));
        return new JsonResponse([], 201);
    }
}
