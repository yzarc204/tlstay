<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
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
        $roomId = $this->route('room')?->id;

        return [
            'room_number' => ['required', 'string', 'max:50'],
            'floor' => ['required', 'integer', 'min:1'],
            'price_per_day' => ['required', 'numeric', 'min:0'],
            'price_per_week' => ['nullable', 'numeric', 'min:0'],
            'price_per_month' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,active'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['nullable'], // Can be either image file or string URL
            'tenant_name' => ['nullable', 'string', 'max:255'],
            'tenant_id' => ['nullable', 'integer', 'exists:users,id'],
            'rental_start_date' => ['nullable', 'date', 'required_if:status,active'],
            'rental_end_date' => ['nullable', 'date', 'after:rental_start_date', 'required_if:status,active'],
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
            'room_number.required' => 'Vui lòng nhập số phòng',
            'floor.required' => 'Vui lòng chọn tầng',
            'floor.integer' => 'Tầng phải là số nguyên',
            'floor.min' => 'Tầng phải lớn hơn 0',
            'price_per_day.required' => 'Vui lòng nhập giá thuê theo ngày',
            'price_per_day.numeric' => 'Giá thuê phải là số',
            'price_per_day.min' => 'Giá thuê phải lớn hơn 0',
            'price_per_week.numeric' => 'Giá thuê theo tuần phải là số',
            'price_per_week.min' => 'Giá thuê theo tuần phải lớn hơn 0',
            'price_per_month.numeric' => 'Giá thuê theo tháng phải là số',
            'price_per_month.min' => 'Giá thuê theo tháng phải lớn hơn 0',
            'status.required' => 'Vui lòng chọn trạng thái',
            'status.in' => 'Trạng thái không hợp lệ',
            'area.numeric' => 'Diện tích phải là số',
            'area.min' => 'Diện tích phải lớn hơn 0',
            'images.max' => 'Tối đa 10 ảnh',
            'images.*.image' => 'File phải là ảnh',
            'images.*.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, webp',
            'images.*.max' => 'Kích thước ảnh tối đa 5MB',
            'rental_start_date.required_if' => 'Vui lòng nhập ngày bắt đầu thuê khi phòng đã được thuê',
            'rental_start_date.date' => 'Ngày bắt đầu thuê không hợp lệ',
            'rental_end_date.required_if' => 'Vui lòng nhập ngày kết thúc thuê khi phòng đã được thuê',
            'rental_end_date.date' => 'Ngày kết thúc thuê không hợp lệ',
            'rental_end_date.after' => 'Ngày kết thúc thuê phải sau ngày bắt đầu thuê',
        ];
    }
}
