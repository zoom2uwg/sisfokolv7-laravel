<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCurriculumCompetencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('competency')?->id;
        $academicYearId = $this->input('academic_year_id');
        $subjectId = $this->input('subject_id');
        $phase = $this->input('phase');

        return [
            'academic_year_id' => ['required', 'exists:tahun_ajaran,id'],
            'subject_id' => ['required', 'exists:mapel,id'],
            'phase' => ['required', 'string', 'max:20'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('curriculum_competencies')
                    ->where(fn ($query) => $query
                        ->where('academic_year_id', $academicYearId)
                        ->where('subject_id', $subjectId)
                        ->where('phase', $phase))
                    ->ignore($id),
            ],
            'description' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'Kode TP',
            'description' => 'Deskripsi Tujuan Pembelajaran',
            'phase' => 'Fase',
        ];
    }
}

