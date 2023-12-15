<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Sanctum::authenticateAccessTokensUsing(
            static function (PersonalAccessToken $accessToken, bool $is_valid) {
                return $accessToken->expired_at ? $is_valid && !$accessToken->expired_at->isPast() : $is_valid;
                // your logic here
            }
        );

        //
    }

    protected function isValidAccessToken($accessToken): bool
    {
        if (! $accessToken) {
            return false;
        }

        $isValid =
            (! $this->expiration || $accessToken->created_at->gt(now()->subMinutes($this->expiration)))
            && $this->hasValidProvider($accessToken->tokenable);

        if (is_callable(Sanctum::$accessTokenAuthenticationCallback)) {
            $isValid = (bool) (Sanctum::$accessTokenAuthenticationCallback)($accessToken, $isValid);
        }

        return $isValid;
    }

}
