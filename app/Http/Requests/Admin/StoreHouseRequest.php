<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreHouseRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'], // Keep for backward compatibility
            'street_detail' => ['required', 'string', 'max:500'],
            'street_id' => ['required', 'exists:addresses,id'],
            'description' => ['nullable', 'string'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string'],
            'existing_images' => ['nullable', 'array'],
            'existing_images.*' => ['string'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
            'featured_images' => ['nullable', 'array'],
            'featured_images.*' => ['string'],
            'featured_image_indices' => ['nullable', 'array'],
            'featured_image_indices.*' => ['integer', 'min:0'],
            'price_per_day' => ['required', 'numeric', 'min:0'],
            'floors' => ['nullable', 'integer', 'min:1'],
            'total_rooms' => ['nullable', 'integer', 'min:0'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
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
            'name.required' => 'Vui lòng nhập tên nhà trọ',
            'name.max' => 'Tên nhà trọ không được vượt quá 255 ký tự',
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự',
            'street_detail.required' => 'Vui lòng nhập địa chỉ cụ thể',
            'street_detail.max' => 'Địa chỉ cụ thể không được vượt quá 500 ký tự',
            'street_id.required' => 'Vui lòng chọn đường',
            'street_id.exists' => 'Đường không tồn tại',
            'images.max' => 'Tối đa 10 ảnh',
            'images.*.image' => 'File phải là ảnh',
            'images.*.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, webp',
            'images.*.max' => 'Kích thước ảnh tối đa 5MB',
            'price_per_day.required' => 'Vui lòng nhập giá thuê theo ngày',
            'price_per_day.numeric' => 'Giá thuê phải là số',
            'price_per_day.min' => 'Giá thuê phải lớn hơn 0',
            'floors.integer' => 'Số tầng phải là số nguyên',
            'floors.min' => 'Số tầng phải lớn hơn 0',
            'total_rooms.integer' => 'Tổng số phòng phải là số nguyên',
            'total_rooms.min' => 'Tổng số phòng phải lớn hơn hoặc bằng 0',
            'latitude.numeric' => 'Vĩ độ phải là số',
            'latitude.between' => 'Vĩ độ phải trong khoảng -90 đến 90',
            'longitude.numeric' => 'Kinh độ phải là số',
            'longitude.between' => 'Kinh độ phải trong khoảng -180 đến 180',
        ];
    }
}
