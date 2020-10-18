<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Billing\Exceptions\NotEnoughTicketsException;
use App\Billing\Exceptions\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Models\Concert;
use App\Models\Order;
use App\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ConcertOrdersController extends Controller
{
    public function store(
        Request $request,
        int $concertId,
        PaymentGateway $paymentGateway
    ): JsonResponse {
        /** @var Concert $concert */
        $concert = Concert::published()->findOrFail($concertId);

        $request->validate([
            'email' => 'required|email',
            'ticket_quantity' => 'required|integer|min:1',
            'payment_token' => 'required|string',
        ]);

        try {
            $ticketQuantity = $request->input('ticket_quantity');
            $email = $request->input('email');
            $paymentToken = $request->input('payment_token');

            $reservation = $concert->reserveTickets($ticketQuantity);

            $paymentGateway->charge($reservation->totalCost(), $paymentToken);

            $order = Order::forTickets($reservation->tickets(), $email, $reservation->totalCost());

            return new JsonResponse($order, Response::HTTP_CREATED);
        } catch (PaymentFailedException $e) {
            $reservation->cancel();
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NotEnoughTicketsException $e) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
