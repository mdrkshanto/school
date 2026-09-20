<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerPolicies();
    }

    /**
     * Explicitly register all module policies against their models.
     */
    protected function registerPolicies(): void
    {
        Gate::policies([
            \App\Models\User::class => \App\Policies\UserPolicy::class,
            \Spatie\Permission\Models\Role::class => \App\Policies\RolePolicy::class,
            \App\Models\Setting::class => \App\Policies\SettingPolicy::class,
            \App\Models\AcademicSession::class => \App\Policies\AcademicSessionPolicy::class,
            \App\Models\SchoolClass::class => \App\Policies\SchoolClassPolicy::class,
            \App\Models\Section::class => \App\Policies\SectionPolicy::class,
            \App\Models\Subject::class => \App\Policies\SubjectPolicy::class,
            \App\Models\Teacher::class => \App\Policies\TeacherPolicy::class,
            \App\Models\Guardian::class => \App\Policies\GuardianPolicy::class,
            \App\Models\Exam::class => \App\Policies\ExamPolicy::class,
            \App\Models\GradeRule::class => \App\Policies\GradeRulePolicy::class,
            \App\Models\Result::class => \App\Policies\ResultPolicy::class,
            \App\Models\FeeStructure::class => \App\Policies\FeeStructurePolicy::class,
            \App\Models\FeeInvoice::class => \App\Policies\FeeInvoicePolicy::class,
            \App\Models\Notice::class => \App\Policies\NoticePolicy::class,
            \App\Models\Event::class => \App\Policies\EventPolicy::class,
            \App\Models\Book::class => \App\Policies\BookPolicy::class,
            \App\Models\BookIssue::class => \App\Policies\BookIssuePolicy::class,
            \App\Models\TransportRoute::class => \App\Policies\TransportRoutePolicy::class,
            \App\Models\Vehicle::class => \App\Policies\VehiclePolicy::class,
        ]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
