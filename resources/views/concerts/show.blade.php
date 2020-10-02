@extends('layouts.master')

@section('body')
<div class="bg-soft p-xs-y-7 full-height">
    <div class="container">
        @include('concerts.partials.card-no-poster', ['concert' => $concert])

        <div class="text-center text-dark-soft wt-medium">
            <p>Powered by TicketBeast</p>
        </div>
    </div>
</div>
@endsection

<h1>{{ $concert->title }}</h1>
<h2>{{ $concert->subtitle }}</h2>
<p>{{ $concert->formatted_date }}</p>
<p>Doors at {{ $concert->formatted_start_time }} </p>
<p>{{ $concert->formatted_ticket_price }}</p>
<p>{{ $concert->venue }}</p>
<p>{{ $concert->venue_address }}</p>
<p>{{ $concert->city }}, {{ $concert->state }} {{ $concert->zip }}</p>
<p>{{ $concert->additional_information }}</p>


@push('beforeScripts')
<script src="https://checkout.stripe.com/checkout.js"></script>
@endpush
