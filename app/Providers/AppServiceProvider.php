<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Application;
use App\Policies\ApplicationPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Discord\DiscordExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

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
        Gate::policy(Application::class, ApplicationPolicy::class);

        Gate::define('review-applications', fn ($user) => $user->isAtLeast(UserRole::Reviewer));
        Gate::define('manage-applications', fn ($user) => $user->isAtLeast(UserRole::Admin));
        Gate::define('manage-settings', fn ($user) => $user->isAtLeast(UserRole::Owner));

        Event::listen(SocialiteWasCalled::class, DiscordExtendSocialite::class.'@handle');
    }
}
