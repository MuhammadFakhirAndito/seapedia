<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReviewRequest extends FormRequest
{
    /**
     * Semua orang boleh submit review
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isGuest = !$this->user();

        return [
            // guest_name wajib kalau tidak login
            'guest_name' => [
                $isGuest ? 'required' : 'nullable',
                'string',
                'max:100',
            ],
            // rating wajib, harus angka 1-5
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            // comment wajib, minimal 3 karakter, maksimal 1000
            'comment'    => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_name.required' => 'Nama reviewer wajib diisi untuk guest.',
            'guest_name.max'      => 'Nama reviewer maksimal 100 karakter.',
            'rating.required'     => 'Rating wajib diisi.',
            'rating.integer'      => 'Rating harus berupa angka.',
            'rating.min'          => 'Rating minimal 1.',
            'rating.max'          => 'Rating maksimal 5.',
            'comment.required'    => 'Komentar wajib diisi.',
            'comment.min'         => 'Komentar minimal 3 karakter.',
            'comment.max'         => 'Komentar maksimal 1000 karakter.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Input tidak valid.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
