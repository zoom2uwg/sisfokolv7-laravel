<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentAchievementRequest extends FormRequest
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
            'achievement_type_id' => ['required', 'exists:achievement_types,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'level' => ['nullable', 'string', 'max:100'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id' => 'Siswa',
            'achievement_type_id' => 'Jenis Prestasi',
            'title' => 'Judul Prestasi',
            'level' => 'Tingkat',
        ];
    }
}

