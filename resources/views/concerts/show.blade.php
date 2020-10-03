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

@push('beforeScripts')
<script src="https://js.stripe.com/v3/"></script>
@endpush
