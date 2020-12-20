<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backstage\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConcertRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required',
            'date' => 'required|date',
            'time' => 'required|date_format:g:ia',
            'venue' => 'required',
            'venue_address' => 'required',
            'city' => 'required',
            'state' => 'required|alpha|size:2',
            'zip' => 'required',
            'ticket_price' => 'required|numeric|min:5',
            'ticket_quantity' => 'required|numeric|min:1',
        ];
    }
}
