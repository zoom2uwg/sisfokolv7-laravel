<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSummativeAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:tahun_ajaran,id'],
            'subject_id' => ['required', 'exists:mapel,id'],
            // [2026-06-25 | AI-Agent] Update classrooms -> kelas
            'classroom_id' => ['required', 'exists:kelas,id'],
            'name' => ['required', 'string', 'max:200'],
            'assessment_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'subject_id' => 'Mapel',
            'classroom_id' => 'Kelas',
            'name' => 'Nama Asesmen',
            'assessment_date' => 'Tanggal Asesmen',
        ];
    }
}

