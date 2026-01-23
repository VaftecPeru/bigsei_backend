<?php

namespace App\Providers;

use App\Events\CourseCompleted;
use App\Listeners\GenerateCertificate;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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
        // Registrar el listener para generación automática de certificados
        Event::listen(
            CourseCompleted::class,
            GenerateCertificate::class
        );
    }
}
