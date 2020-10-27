<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Concert;

class ConcertsController extends Controller
{
    public function show(int $concertId)
    {
        /** @var Concert $concert */
        $concert = Concert::published()->findOrFail($concertId);

        return view('concerts.show', ['concert' => $concert]);
    }
}
