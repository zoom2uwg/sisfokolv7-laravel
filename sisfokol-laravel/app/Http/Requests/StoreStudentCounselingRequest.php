<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentCounselingRequest extends FormRequest
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
            'counseling_type_id' => ['required', 'exists:counseling_types,id'],
            'counselor_teacher_id' => ['required', 'exists:counselor_teachers,id'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'follow_up' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id' => 'Siswa',
            'counseling_type_id' => 'Jenis Pembinaan',
            'counselor_teacher_id' => 'Guru BK',
            'date' => 'Tanggal',
        ];
    }
}

