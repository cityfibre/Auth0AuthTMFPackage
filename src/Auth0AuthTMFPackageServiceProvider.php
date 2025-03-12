<?php

namespace cityfibre\auth0authtmfpackage;

use Auth0\SDK\Configuration\SdkConfiguration;
use cityfibre\auth0authtmfpackage\Http\Middleware\Auth0AuthenticateTMFMiddleware;
use cityfibre\auth0authtmfpackage\Models\Auth0;
use cityfibre\auth0authtmfpackage\Repositories\Auth0Repository;
use cityfibre\auth0authtmfpackage\Services\Auth0Service;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use willfd\auth0middlewarepackage\Services\AuthenticationService;
class Auth0AuthTMFPackageServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected bool $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/Auth0AuthTMFMiddleware.php' => config_path('Auth0AuthTMFMiddleware.php'),
        ]);

        Log::debug('config', ['domain' => config('Auth0AuthTMFMiddleware.domain')]);

    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/Auth0AuthTMFMiddleware.php', 'Auth0AuthTMFMiddleware');

        Log::debug('config', ['domain' => config('Auth0AuthTMFMiddleware.domain')] );
        $sdkConfig = $this->setUpSDKConfiguration();

        $this->app->singleton(Auth0AuthenticateTMFMiddleware::class, function ($app) use ($sdkConfig) {
            return new Auth0AuthenticateTMFMiddleware(
                new Auth0Service( app('log'), new Auth0Repository( new Auth0()) ),
                new AuthenticationService( app('log') ),
                config('Auth0AuthTMFMiddleware.adminScopes'),
                $sdkConfig,
                app('log')
            );
        });
    }

    protected function setUpSDKConfiguration(): ?SdkConfiguration
    {
        try {
            $domain = config('Auth0AuthTMFMiddleware.domain');
            if ($domain == '') {
                throw new Exception("Auth0AuthTMFMiddleware ERROR: Domain not set");
            }

            $clientId = config('Auth0AuthTMFMiddleware.clientId');
            if ($clientId == '') {
                throw new Exception("Auth0AuthTMFMiddleware ERROR: Client Id not set");
            }

            $cookieSecret = config('Auth0AuthTMFMiddleware.cookieSecret');
            if ($cookieSecret == '') {
                throw new Exception("Auth0AuthTMFMiddleware ERROR: Client Secret not set");
            }

            $audience = config('Auth0AuthTMFMiddleware.audience');
            if ($audience == ['']) {
                throw new Exception("Auth0AuthTMFMiddleware ERROR: Audience not set");
            }

            return new SdkConfiguration([
                'domain' => $domain,
                'clientId' => $clientId,
                'cookieSecret' => $cookieSecret,
                'audience' => $audience,
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return null;
        }
    }

}