<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentSavingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // [2026-06-25 | AI-Agent] Update students -> siswa
            'student_id' => ['required', 'exists:siswa,id'],
            'date' => ['required', 'date'],
            'is_debit' => ['required', 'boolean'],
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id' => 'Siswa',
            'date' => 'Tanggal',
            'is_debit' => 'Jenis',
            'amount' => 'Jumlah',
        ];
    }
}
