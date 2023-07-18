<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TourListRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'priceTo' => 'numeric',
            'priceFrom' => 'numeric',
            'dateTo' => 'date',
            'dateFrom' => 'date',
            'sortBy' => Rule::in(['price']),
            'sortByOrder' => Rule::in(['asc', 'desc']),
        ];
    }

    public function messages(): array
    {

        return [
            'sortBy' => "The SortBy Parameter accept only 'price' value",
            'sortByOrder' => "The sortByOrder Parameter accept only 'asc', 'desc' value",
        ];
    }
}
