<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Project;
use App\Models\StudentAnswer;
use App\Policies\ActivityPolicy;
use App\Policies\AssessmentPolicy;
use App\Policies\LearningUnitPolicy;
use App\Policies\ModulePolicy;
use App\Policies\ProjectPolicy;
use App\Policies\StudentAnswerPolicy;
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
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Gate::policy(Module::class, ModulePolicy::class);
        Gate::policy(LearningUnit::class, LearningUnitPolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Assessment::class, AssessmentPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(StudentAnswer::class, StudentAnswerPolicy::class);

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
