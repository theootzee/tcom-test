<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
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
            // 'date_from' => 'required|date_format:YYYY-mm-dd',
            // 'date_to' => 'required|date_format:YYYY-mm-dd|gt:date_from',
            // // 'user_id' => 'required|exists:users,id',
            // // 'leave_type_id' => 'required|exists:leave_types,id'
        ];
    }
}
