<?php

namespace App\Http\Requests;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isPartner() && (bool) $this->user()->store;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'model_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:1000', 'max:1000000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'condition' => ['required', Rule::enum(ProductCondition::class)],
            'status' => ['required', Rule::in([ProductStatus::Active->value, ProductStatus::Inactive->value])],
            'specs' => ['nullable', 'array'],
            'specs.*' => ['nullable', 'string', 'max:255'],
            'compatibilities' => ['nullable', 'array', 'max:10'],
            'compatibilities.*.brand_id' => ['nullable', 'exists:brands,id'],
            'compatibilities.*.model_name' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'primary_image_id' => ['nullable', 'integer'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.max' => 'Maksimal 5 foto per produk.',
            'images.*.max' => 'Ukuran setiap foto maksimal 2 MB.',
            'price.min' => 'Harga minimal Rp 1.000.',
        ];
    }
}
