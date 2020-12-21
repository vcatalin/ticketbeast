<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Backstage\Requests\StoreConcertRequest;
use App\Http\Controllers\Controller;
use App\Models\Concert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ConcertsController extends Controller
{
    public function create()
    {
        return view('backstage.concerts.create');
    }

    public function store(
        Request $request,
        StoreConcertRequest $storeConcertRequest
    ): RedirectResponse {
        // TODO: Refactor $concert to DTO
        $validated = $storeConcertRequest->validated();

        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->create([
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'date' => Carbon::parse(vsprintf('%s %s', [
                $request->input('date'),
                $request->input('time')
            ])),
            'ticket_price' => $request->input('ticket_price') * 100,
            'venue' => $request->input('venue'),
            'venue_address' => $request->input('venue_address'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'zip' => $request->input('zip'),
            'additional_information' => $request->input('additional_information'),
        ])->addTickets((int) $request->input('ticket_quantity'));

        $concert->publish();

        return redirect()->route('concerts.show', ['concertId' => $concert->id]);
    }

    public function index()
    {
        return view('backstage.concerts.index', ['concerts' => Auth::user()->concerts]);
    }

    public function edit(int $concertId)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->findOrFail($concertId);

        abort_if($concert->isPublished(), Response::HTTP_FORBIDDEN);

        return view('backstage.concerts.edit', [
            'concert' => $concert
        ]);
    }
}
