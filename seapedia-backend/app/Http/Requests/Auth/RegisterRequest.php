<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // siapa saja boleh register, ini bukan endpoint yang butuh login
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            // roles: array berisi 'buyer'/'seller'/'driver'. Minimal 1, tidak boleh 'admin'
            // (admin tidak boleh didaftarkan lewat form publik, sesuai dokumen).
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'in:buyer,seller,driver'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah digunakan, silakan pilih username lain.',
            'email.unique' => 'Email sudah terdaftar.',
            'roles.required' => 'Pilih minimal satu role (Pembeli/Penjual/Pengantar).',
            'roles.*.in' => 'Role tidak valid.',
        ];
    }
}
