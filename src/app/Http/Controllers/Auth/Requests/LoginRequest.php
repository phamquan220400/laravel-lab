<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Context;

class LoginRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'username' => 'required',
            'password' => [
                'required',
                'string'
            ],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        Context::add('event', array_merge(Context::get('event', []), [
            'action' => __('login.failed_validation'),
            'error' => $validator->errors(),
            'request' => request()->all()
        ]));

        throw new HttpResponseException(response()->json([
            'error' => $validator->errors()
        ], 403));
    }
}
