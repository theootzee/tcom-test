<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "firstname" => "regex:/^([A-ZŠĐČĆŽ][a-zšđčćž\-']{2,50})(\s[A-ZŠĐČĆŽ][a-zšđčćž\-']{2,50})*$/",
            "lastname" => "regex:/^([A-ZŠĐČĆŽ][a-zšđčćž\-']{2,100})(\s[A-ZŠĐČĆŽ][a-zšđčćž\-']{2,100})*$/",
            "email" => "email",
            "role_id" => "required",
            "team_id" => "sometimes",
        ];
    }
}
