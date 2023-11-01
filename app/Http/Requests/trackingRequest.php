<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class trackingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'client_id' => 'required|uuid|exists:users',
            'tracking_number' => 'required|numeric|unique:tracking_queues',
            'ship_method' => 'required'
        ];
    }





    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'client_id.required'        => 'client_id is required',
            'client_id.uuid'            => 'client_id is not valid',
            'client_id.exists'          => 'client_id invalid',
            'tracking_number.required'  => 'tracking number is required',
            'tracking_number.unique'    => 'tracking number already in queue',
        ];
    }


}
