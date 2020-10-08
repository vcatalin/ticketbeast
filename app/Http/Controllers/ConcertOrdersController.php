<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Billing\Exeptions\PaymentFailedException;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Models\Concert;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ConcertOrdersController extends Controller
{
    public function store(
        Request $request,
        int $concertId,
        PaymentGateway $paymentGateway
    ) {
        $concert = Concert::published()->findOrFail($concertId);

        $request->validate([
            'email' => 'required|email',
            'ticket_quantity' => 'required|integer|min:1',
            'payment_token' => 'required|string',
        ]);

        try {
            $ticketQuantity = $request->input('ticket_quantity');
            $email = $request->input('email');

            // Charging the customer
            $amount = $ticketQuantity * $concert->ticket_price;
            $paymentGateway->charge($amount, $request->input('payment_token'));

            // Creating the order
            $order = $concert->orderTickets(
                $email,
                $ticketQuantity
            );

            return new JsonResponse([], Response::HTTP_CREATED);
        } catch (PaymentFailedException $e) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
