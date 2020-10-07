<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Models\Concert;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ConcertOrdersController extends Controller
{
    public function store(
        Request $request,
        int $concertId,
        PaymentGateway $paymentGateway
    ) {
        $concert = Concert::find($concertId);

        $request->validate([
            'email' => 'required|email',
            'ticket_quantity' => 'required|integer|min:1',
            'payment_token' => 'required|string',
        ]);

        // Chargin the customer
        $ticketQuantity = $request->input('ticket_quantity');
        $email = $request->input('email');

        $amount = $ticketQuantity * $concert->ticket_price;
        $paymentGateway->charge($amount, $request->input('payment_token'));

        // Creating the order
        $order = $concert->orderTickets(
            $email,
            $ticketQuantity
        );

        return new JsonResponse([], Response::HTTP_ACCEPTED);
    }
}
