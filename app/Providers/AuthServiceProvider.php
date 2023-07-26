<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Notifications\ResetPasswordNotification;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('hq', fn($user) => $user->isHQ());
        Gate::define('region', fn($user) => $user->isRegion());
        Gate::define('business_hub', fn($user) => $user->isBhub());
        Gate::define('service_center', fn($user) => $user->isSCenter());

        //AD Integration with on-prem Local IP
        $this->registerPolicies();
        Auth::viaRequest('adldap', function ($request) {
            return Auth::guard('ad')->user() ?: null;
        });

        //password reset link
        // ResetPassword::createUrlUsing(function ($user, string $token) {
        //     return 'https://example.com/reset-password?token='.$token;
        // });

    }
}
