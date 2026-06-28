<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi role dicek di middleware, bukan di sini
    }

    public function rules(): array
    {
        // Saat UPDATE, store milik user ini sendiri boleh dikecualikan dari
        // pengecekan unique (supaya bisa save ulang dengan nama yang sama).
        $currentStoreId = $this->user()->store?->id;

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:150',
                Rule::unique('stores', 'name')->ignore($currentStoreId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama toko ini sudah digunakan oleh penjual lain. Silakan pilih nama lain.',
        ];
    }
}
