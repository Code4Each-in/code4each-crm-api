<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        VerifyEmail::createUrlUsing(function ($notifiable) {
        $params = [
            "expires" => Carbon::now()
            ->addMinutes(60)
            ->getTimestamp(),
            "id" => $notifiable->getKey(),
            "hash" => sha1($notifiable->getEmailForVerification()),
        ];

        ksort($params);

        // then create API url for verification. my API have `/api` prefix,
        // so I don't want to show that url to users
        $url = \URL::route("verification.verify", $params, true);

        // get APP_KEY from config and create signature
        $key = config("app.key");
        $signature = hash_hmac("sha256", $url, $key);

        // generate url for yous SPA page to send it to user
            return config("app.frontend_url") .
            "/email/verify/" .
            '?id='.
            $params["id"] .
            "&expires=" .
            $params["expires"] .
            "&hash=" .
            $params["hash"] .
            "&signature=" .
            $signature;
        });
    }
}
