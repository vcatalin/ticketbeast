<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Backstage\Requests\StoreConcertRequest;
use App\Http\Controllers\Backstage\Requests\UpdateConcertRequest;
use App\Http\Controllers\Controller;
use App\Models\Concert;
use Illuminate\Http\RedirectResponse;
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
        StoreConcertRequest $concertRequest
    ): RedirectResponse {
        // TODO: Refactor $concert to DTO
        $validated = $concertRequest->validationData();

        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->create([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'date' => Carbon::parse(vsprintf('%s %s', [
                $validated['date'],
                $validated['time'],
            ])),
            'ticket_price' => $validated['ticket_price'] * 100,
            'venue' => $validated['venue'],
            'venue_address' => $validated['venue_address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip' => $validated['zip'],
            'additional_information' => $validated['additional_information'],
        ])->addTickets((int) $validated['ticket_quantity']);

        $concert->publish();

        return redirect()->route('concerts.show', ['concertId' => $concert->id]);
    }

    public function index()
    {
        return view('backstage.concerts.index', [
            'publishedConcerts' => Auth::user()->concerts->filter->isPublished(),
            'unpublishedConcerts' => Auth::user()->concerts->reject->isPublished(),
        ]);
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

    public function update(
        int $concertId,
        UpdateConcertRequest $concertRequest
    ): RedirectResponse {
        $validated = $concertRequest->validationData();

        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->findOrFail($concertId);

        abort_if($concert->isPublished(), Response::HTTP_FORBIDDEN);

        $concert->update([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'date' => Carbon::parse(vsprintf('%s %s', [
                $validated['date'],
                $validated['time'],
            ])),
            'ticket_price' => $validated['ticket_price'] * 100,
            'venue' => $validated['venue'],
            'venue_address' => $validated['venue_address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip' => $validated['zip'],
            'additional_information' => $validated['additional_information'],
        ]);

        return redirect()->route('backstage.concerts.index');
    }
}
