<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backstage\Dto;

use App\Http\Controllers\Backstage\Requests\UpdateConcertRequest;
use Spatie\DataTransferObject\DataTransferObject;

class UpdateConcertData extends DataTransferObject
{
    public string $title;
    public string $subtitle;
    public string $date;
    public string $time;
    public float $ticketPrice;
    public string $venue;
    public string $venueAddress;
    public string $city;
    public string $state;
    public string $zip;
    public string $additionalInformation;

    public static function fromRequest(UpdateConcertRequest $request): self
    {
        return new self([
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'date' => $request->input('date'),
            'time' => $request->input('time'),
            'ticketPrice' => (float) $request->input('ticket_price'),
            'venue' => $request->input('venue'),
            'venueAddress' => $request->input('venue_address'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'zip' => $request->input('zip'),
            'additionalInformation' => $request->input('additional_information'),
        ]);
    }
}
