<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentViolationRequest extends FormRequest
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
            'violation_point_id' => ['required', 'exists:violation_points,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id' => 'Siswa',
            'violation_point_id' => 'Pelanggaran',
            'employee_id' => 'Pelapor',
            'date' => 'Tanggal',
        ];
    }
}

