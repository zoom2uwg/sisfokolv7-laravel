<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('classroom')?->id;
        $academicYearId = $this->input('academic_year_id');

        return [
            'academic_year_id' => ['required', 'exists:tahun_ajaran,id'],
            'name' => [
                'required',
                'string',
                'max:50',
                // [2026-06-25 | AI-Agent] Update classrooms -> kelas
                Rule::unique('kelas')->where(fn ($query) => $query->where('academic_year_id', $academicYearId))->ignore($id),
            ],
            'level' => ['required', 'string', 'max:20'],
            'major' => ['nullable', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1'],
            'homeroom_teacher_id' => ['nullable', 'exists:employees,id'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Kelas',
            'level' => 'Tingkat',
            'major' => 'Jurusan',
            'capacity' => 'Kapasitas',
            'homeroom_teacher_id' => 'Wali Kelas',
        ];
    }
}

