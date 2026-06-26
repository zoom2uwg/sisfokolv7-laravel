<?php

namespace App\Modules\Evaluation\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\FormativeAssessment;
use App\Models\FormativeAssessmentScore;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentSemesterScore;
use App\Models\Subject;
use App\Models\SummativeAssessment;
use App\Models\SummativeAssessmentScore;
use App\Modules\Academic\Models\Semester;
use App\Modules\Evaluation\Requests\BatchGradeRequest;
use App\Modules\Evaluation\Services\GradeCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeEntryController extends Controller
{
    protected GradeCalculatorService $calculator;

    public function __construct(GradeCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Display selection page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // If SuperAdmin or Admin, they see all classrooms and subjects
        if ($user->isSuperAdmin() || $user->hasRole(['admin_sekolah', 'admin']) || $user->tipe === 'admin_sekolah') {
            $classrooms = Classroom::all();
            $subjects = Subject::all();
        } else {
            // It's a teacher, filter by their schedule
            $employeeId = $user->userable_id;
            $schedules = Schedule::where('employee_id', $employeeId)->with(['classroom', 'subject'])->get();
            $classrooms = $schedules->pluck('classroom')->unique('id')->values();
            $subjects = $schedules->pluck('subject')->unique('id')->values();
        }

        return view('evaluation.grade-entry.index', compact('classrooms', 'subjects'));
    }

    /**
     * Display interactive grading grid form.
     */
    public function form(Request $request)
    {
        $request->validate([
            // [2026-06-25 | AI-Agent] Update classrooms -> kelas
            'classroom_id' => 'required|exists:kelas,id',
            'subject_id' => 'required|exists:mapel,id',
        ]);

        $classroomId = $request->input('classroom_id');
        $subjectId = $request->input('subject_id');

        $classroom = Classroom::findOrFail($classroomId);
        $subject = Subject::findOrFail($subjectId);

        $academicYear = AcademicYear::active();
        if (!$academicYear) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $semester = Semester::where('aktif', true)->first();
        if (!$semester) {
            return redirect()->back()->with('error', 'Tidak ada semester aktif.');
        }

        // Fetch students enrolled in this classroom
        $students = Student::where('classroom_id', $classroomId)->get();

        // Fetch assessments in this date range
        $formativeAssessments = FormativeAssessment::where('classroom_id', $classroomId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYear->id)
            ->whereBetween('assessment_date', [$semester->tanggal_mulai, $semester->tanggal_selesai])
            ->get();

        $summativeAssessments = SummativeAssessment::where('classroom_id', $classroomId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYear->id)
            ->whereBetween('assessment_date', [$semester->tanggal_mulai, $semester->tanggal_selesai])
            ->get();

        // Fetch scores
        $formativeScores = FormativeAssessmentScore::whereIn('assessment_id', $formativeAssessments->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $summativeScores = SummativeAssessmentScore::whereIn('assessment_id', $summativeAssessments->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $semesterScores = StudentSemesterScore::whereIn('student_id', $students->pluck('id'))
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYear->id)
            ->where('semester', $semester->nama)
            ->get()
            ->keyBy('student_id');

        // Compile student grid data for AlpineJS
        $gridData = $students->map(function ($student) use ($formativeAssessments, $summativeAssessments, $formativeScores, $summativeScores, $semesterScores) {
            $studentFormativeScores = [];
            foreach ($formativeAssessments as $fa) {
                $scoreRecord = $formativeScores->get($student->id)?->where('assessment_id', $fa->id)->first();
                $studentFormativeScores[$fa->id] = $scoreRecord ? floatval($scoreRecord->score) : '';
            }

            $studentSummativeScores = [];
            foreach ($summativeAssessments as $sa) {
                $scoreRecord = $summativeScores->get($student->id)?->where('assessment_id', $sa->id)->first();
                $studentSummativeScores[$sa->id] = $scoreRecord ? floatval($scoreRecord->score) : '';
            }

            $semesterScoreRecord = $semesterScores->get($student->id);

            return [
                'id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->name,
                'formative_scores' => $studentFormativeScores,
                'summative_scores' => $studentSummativeScores,
                'semester_score' => $semesterScoreRecord ? floatval($semesterScoreRecord->score) : '',
                'predicate' => $semesterScoreRecord ? $semesterScoreRecord->predicate : '',
            ];
        });

        return view('evaluation.grade-entry.form', compact(
            'classroom',
            'subject',
            'academicYear',
            'semester',
            'formativeAssessments',
            'summativeAssessments',
            'gridData'
        ));
    }

    /**
     * Store new assessment header via AJAX.
     */
    public function storeAssessment(Request $request)
    {
        $validated = $request->validate([
            // [2026-06-25 | AI-Agent] Update classrooms -> kelas
            'classroom_id' => 'required|exists:kelas,id',
            'subject_id' => 'required|exists:mapel,id',
            'type' => 'required|in:formative,summative',
            'name' => 'required|string|max:255',
            'assessment_date' => 'required|date',
        ]);

        $academicYear = AcademicYear::active();

        if ($validated['type'] === 'formative') {
            $assessment = FormativeAssessment::create([
                'academic_year_id' => $academicYear->id,
                'subject_id' => $validated['subject_id'],
                'classroom_id' => $validated['classroom_id'],
                'name' => $validated['name'],
                'assessment_date' => $validated['assessment_date'],
            ]);
        } else {
            $assessment = SummativeAssessment::create([
                'academic_year_id' => $academicYear->id,
                'subject_id' => $validated['subject_id'],
                'classroom_id' => $validated['classroom_id'],
                'name' => $validated['name'],
                'assessment_date' => $validated['assessment_date'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'assessment' => $assessment
        ]);
    }

    /**
     * Store student grades in batch via AJAX.
     */
    public function storeScores(BatchGradeRequest $request)
    {
        $validated = $request->validated();
        $academicYear = AcademicYear::active();
        $semester = Semester::where('aktif', true)->first();

        $scores = $validated['scores'];
        $assessmentId = $validated['assessment_id'];
        $type = $validated['type'];

        foreach ($scores as $s) {
            // Null or empty values are treated as 0 or not saved? Let's check.
            $scoreVal = $s['score'] !== '' ? floatval($s['score']) : 0.0;

            if ($type === 'formative') {
                FormativeAssessmentScore::updateOrCreate([
                    'assessment_id' => $assessmentId,
                    'student_id' => $s['student_id'],
                ], [
                    'score' => $scoreVal,
                ]);
            } else {
                SummativeAssessmentScore::updateOrCreate([
                    'assessment_id' => $assessmentId,
                    'student_id' => $s['student_id'],
                ], [
                    'score' => $scoreVal,
                ]);
            }

            // Recalculate final semester grades for the student
            $student = Student::find($s['student_id']);
            $this->calculator->saveSemesterScore(
                $student,
                $validated['subject_id'],
                $validated['classroom_id'],
                $academicYear,
                $semester
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Nilai berhasil disimpan dan nilai akhir semester diperbarui.'
        ]);
    }
}

