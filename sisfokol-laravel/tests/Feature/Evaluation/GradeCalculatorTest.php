<?php

namespace Tests\Feature\Evaluation;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\FormativeAssessment;
use App\Models\FormativeAssessmentScore;
use App\Models\Student;
use App\Models\StudentSemesterScore;
use App\Models\Subject;
use App\Models\SummativeAssessment;
use App\Models\SummativeAssessmentScore;
use App\Models\User;
use App\Modules\Academic\Models\Semester;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantSetting;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $teacher;
    protected Student $student;
    protected Classroom $classroom;
    protected Subject $subject;
    protected AcademicYear $academicYear;
    protected Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Tenant
        $this->tenant = Tenant::create([
            'nama' => 'Demo School',
            'npsn' => '11111111',
            'domain' => 'demo.school.test',
        ]);

        // Initialize Tenant Context
        app(TenantContext::class)->set($this->tenant->id);

        // 2. Create User (Teacher)
        $this->teacher = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // 3. Create Academic Year
        $this->academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        // [2026-06-25 | AI-Agent] Avoid duplicate/unique key violation on unified table, fetch existing mapped record.
        // $tahunAjaran = \App\Modules\Academic\Models\TahunAjaran::create([
        //     'id' => $this->academicYear->id,
        //     'tenant_id' => $this->tenant->id,
        //     'nama' => '2026/2027',
        //     'tanggal_mulai' => '2026-07-01',
        //     'tanggal_selesai' => '2027-06-30',
        //     'aktif' => true,
        // ]);
        $tahunAjaran = \App\Modules\Academic\Models\TahunAjaran::find($this->academicYear->id);

        // 4. Create Semester (Ganjil)
        $this->semester = Semester::create([
            'tenant_id' => $this->tenant->id,
            'tahun_ajaran_id' => $tahunAjaran->id,
            'nama' => 1, // Semester 1 / Ganjil
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'aktif' => true,
        ]);

        // 5. Create Classroom
        $this->classroom = Classroom::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Kelas X-A',
            'level' => '10',
        ]);

        // 6. Create Subject
        $this->subject = Subject::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Matematika',
            'code' => 'MTK',
        ]);

        // 7. Create Student
        $this->student = Student::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'Budi Santoso',
            'nis' => '2026001',
            'gender' => 'L',
            'status' => 'aktif',
        ]);
    }

    /** @test */
    public function it_calculates_formative_average_correctly()
    {
        // Create 2 Formative Assessments
        $fa1 = FormativeAssessment::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'Tugas 1',
            'assessment_date' => '2026-08-10',
        ]);

        $fa2 = FormativeAssessment::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'Tugas 2',
            'assessment_date' => '2026-09-15',
        ]);

        // Scores
        FormativeAssessmentScore::create([
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $fa1->id,
            'student_id' => $this->student->id,
            'score' => 80.00,
        ]);

        FormativeAssessmentScore::create([
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $fa2->id,
            'student_id' => $this->student->id,
            'score' => 90.00,
        ]);

        $service = app(\App\Modules\Evaluation\Services\GradeCalculatorService::class);
        $avg = $service->calculateFormativeAverage($this->student, $this->subject->id, $this->classroom->id, $this->academicYear, $this->semester);

        $this->assertEquals(85.00, $avg);
    }

    /** @test */
    public function it_calculates_summative_average_correctly()
    {
        // Create UTS & UAS Summative Assessments
        $sa1 = SummativeAssessment::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'UTS',
            'assessment_date' => '2026-10-05',
        ]);

        $sa2 = SummativeAssessment::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'UAS',
            'assessment_date' => '2026-12-10',
        ]);

        // Scores
        SummativeAssessmentScore::create([
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $sa1->id,
            'student_id' => $this->student->id,
            'score' => 70.00,
        ]);

        SummativeAssessmentScore::create([
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $sa2->id,
            'student_id' => $this->student->id,
            'score' => 80.00,
        ]);

        $service = app(\App\Modules\Evaluation\Services\GradeCalculatorService::class);
        $avg = $service->calculateSummativeAverage($this->student, $this->subject->id, $this->classroom->id, $this->academicYear, $this->semester);

        $this->assertEquals(75.00, $avg);
    }

    /** @test */
    public function it_calculates_semester_score_using_default_weights()
    {
        // Default weights: Formative 40%, Summative 60%
        // Formative Average = 80
        // Summative Average = 70
        // Expected Final = (80 * 0.4) + (70 * 0.6) = 32 + 42 = 74

        $fa = FormativeAssessment::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'Tugas',
            'assessment_date' => '2026-08-10',
        ]);

        FormativeAssessmentScore::create([
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $fa->id,
            'student_id' => $this->student->id,
            'score' => 80.00,
        ]);

        $sa = SummativeAssessment::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'UTS',
            'assessment_date' => '2026-10-05',
        ]);

        SummativeAssessmentScore::create([
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $sa->id,
            'student_id' => $this->student->id,
            'score' => 70.00,
        ]);

        $service = app(\App\Modules\Evaluation\Services\GradeCalculatorService::class);
        $result = $service->calculateSemesterScore($this->student, $this->subject->id, $this->classroom->id, $this->academicYear, $this->semester);

        $this->assertEquals(74.00, $result['final_score']);
        $this->assertEquals('C', $result['predicate']); // 74 is C (70-79)
    }

    /** @test */
    public function it_calculates_semester_score_using_custom_weights()
    {
        // Mock custom weights in TenantContext settings
        // Formative weight: 50%, Summative weight: 50%
        app(TenantContext::class)->set($this->tenant->id, null, [
            'weight_formative' => 0.50,
            'weight_summative' => 0.50,
        ]);

        // Formative Average = 80
        // Summative Average = 60
        // Expected Final = (80 * 0.5) + (60 * 0.5) = 40 + 30 = 70

        $fa = FormativeAssessment::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'Tugas',
            'assessment_date' => '2026-08-10',
        ]);

        FormativeAssessmentScore::create([
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $fa->id,
            'student_id' => $this->student->id,
            'score' => 80.00,
        ]);

        $sa = SummativeAssessment::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'UTS',
            'assessment_date' => '2026-10-05',
        ]);

        SummativeAssessmentScore::create([
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $sa->id,
            'student_id' => $this->student->id,
            'score' => 60.00,
        ]);

        $service = app(\App\Modules\Evaluation\Services\GradeCalculatorService::class);
        $result = $service->calculateSemesterScore($this->student, $this->subject->id, $this->classroom->id, $this->academicYear, $this->semester);

        $this->assertEquals(70.00, $result['final_score']);
        $this->assertEquals('C', $result['predicate']);
    }

    /** @test */
    public function it_assigns_correct_predicates()
    {
        $service = app(\App\Modules\Evaluation\Services\GradeCalculatorService::class);

        $this->assertEquals('A', $service->determinePredicate(95));
        $this->assertEquals('A', $service->determinePredicate(90));
        $this->assertEquals('B', $service->determinePredicate(85));
        $this->assertEquals('B', $service->determinePredicate(80));
        $this->assertEquals('C', $service->determinePredicate(75));
        $this->assertEquals('C', $service->determinePredicate(70));
        $this->assertEquals('D', $service->determinePredicate(69));
        $this->assertEquals('D', $service->determinePredicate(50));
    }

    /** @test */
    public function it_saves_semester_score_to_database()
    {
        $fa = FormativeAssessment::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'Tugas',
            'assessment_date' => '2026-08-10',
        ]);

        FormativeAssessmentScore::create([
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $fa->id,
            'student_id' => $this->student->id,
            'score' => 90.00,
        ]);

        $sa = SummativeAssessment::create([
            'tenant_id' => $this->tenant->id,
            'academic_year_id' => $this->academicYear->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'UTS',
            'assessment_date' => '2026-10-05',
        ]);

        SummativeAssessmentScore::create([
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $sa->id,
            'student_id' => $this->student->id,
            'score' => 90.00,
        ]);

        $service = app(\App\Modules\Evaluation\Services\GradeCalculatorService::class);
        $scoreObj = $service->saveSemesterScore($this->student, $this->subject->id, $this->classroom->id, $this->academicYear, $this->semester);

        $this->assertInstanceOf(StudentSemesterScore::class, $scoreObj);
        $this->assertDatabaseHas('student_semester_scores', [
            'tenant_id' => $this->tenant->id,
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'semester' => $this->semester->nama,
            'score' => 90.00,
            'predicate' => 'A',
        ]);
    }
}
