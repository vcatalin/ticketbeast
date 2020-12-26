<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backstage\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConcertRequest extends FormRequest
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
            'subtitle' => 'nullable',
            'date' => 'required',
            'time' => 'required',
            'venue' => 'required',
            'venue_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'ticket_price' => 'required',
        ];
    }
}
