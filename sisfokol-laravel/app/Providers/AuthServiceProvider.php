<?php

namespace App\Providers;

use App\Models\Absence;
use App\Models\Classroom;
use App\Models\CurriculumCompetency;
use App\Models\Permit;
use App\Models\Schedule;
use App\Models\StudentPayment;
use App\Models\StudentSaving;
use App\Models\StudentViolation;
use App\Models\Subject;
use App\Models\TeacherAgenda;
use App\Models\User;
use App\Policies\AbsencePolicy;
use App\Policies\ClassroomPolicy;
use App\Policies\PermitPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\StudentPaymentPolicy;
use App\Policies\StudentSavingPolicy;
use App\Policies\StudentViolationPolicy;
use App\Policies\SubjectPolicy;
use App\Policies\TeacherAgendaPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Classroom::class => ClassroomPolicy::class,
        Subject::class => SubjectPolicy::class,
        Schedule::class => SchedulePolicy::class,
        TeacherAgenda::class => TeacherAgendaPolicy::class,
        Absence::class => AbsencePolicy::class,
        Permit::class => PermitPolicy::class,
        StudentViolation::class => StudentViolationPolicy::class,
        StudentPayment::class => StudentPaymentPolicy::class,
        StudentSaving::class => StudentSavingPolicy::class,
        \App\Modules\Finance\Models\ItemPembayaran::class => \App\Modules\Finance\Policies\ItemPembayaranPolicy::class,
        \App\Modules\Finance\Models\Pembayaran::class => \App\Modules\Finance\Policies\PembayaranPolicy::class,
        \App\Modules\Finance\Models\TabunganSiswa::class => \App\Modules\Finance\Policies\TabunganPolicy::class,
        \App\Plugins\Kurikulum\Models\Kurikulum::class => \App\Plugins\Kurikulum\Policies\KurikulumPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
