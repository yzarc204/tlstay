<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
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
            'type' => ['required', 'in:ward,street'],
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:addresses,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Vui lòng chọn loại địa điểm',
            'type.in' => 'Loại địa điểm không hợp lệ',
            'name.required' => 'Vui lòng nhập tên địa điểm',
            'name.max' => 'Tên địa điểm không được vượt quá 255 ký tự',
            'parent_id.exists' => 'Địa điểm cha không tồn tại',
        ];
    }
}
