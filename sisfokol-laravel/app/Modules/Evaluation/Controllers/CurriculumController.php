<?php

namespace App\Modules\Evaluation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CurriculumCompetency;
use App\Modules\Academic\Models\Mapel;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    public function index(Request $request)
    {
        $mapelId = $request->get('mapel_id');
        $competencies = CurriculumCompetency::when($mapelId, fn($q) => $q->where('subject_id', $mapelId))
            ->with(['subject'])
            ->get();
        $mapels = Mapel::all();

        return view('evaluation.curriculum.index', compact('competencies', 'mapels', 'mapelId'));
    }

    public function create()
    {
        $mapels = Mapel::all();
        return view('evaluation.curriculum.create', compact('mapels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:mapel,id', // subject_id maps to subjects table in core evaluation schema
            'phase' => 'required|string|max:10',
            'code' => 'required|string|max:50',
            'description' => 'required|string',
        ]);

        $academicYear = \App\Models\AcademicYear::active();

        CurriculumCompetency::create(array_merge($validated, [
            'academic_year_id' => $academicYear?->id,
        ]));

        return redirect()->route('evaluation.curriculum.index')->with('success', 'Kompetensi kurikulum berhasil ditambahkan.');
    }
}

