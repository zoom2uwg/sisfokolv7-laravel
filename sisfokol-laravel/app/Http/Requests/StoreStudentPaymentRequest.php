<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:tahun_ajaran,id'],
            // [2026-06-25 | AI-Agent] Update students -> siswa
            'student_id' => ['required', 'exists:siswa,id'],
            'payment_date' => ['required', 'date'],
            'total' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,transfer'],
            'note' => ['nullable', 'string'],
            'bills' => ['required', 'array'],
            'bills.*' => ['numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id' => 'Siswa',
            'payment_date' => 'Tanggal Bayar',
            'total' => 'Total Bayar',
            'payment_method' => 'Metode Pembayaran',
            'bills' => 'Tagihan',
        ];
    }
}

