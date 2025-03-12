<?php

namespace cityfibre\auth0authtmfpackage\Http\Middleware;

use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\InvalidTokenException;
use cityfibre\auth0authtmfpackage\Exceptions\Auth0DataException;
use cityfibre\auth0authtmfpackage\Services\Auth0Service;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;
use willfd\auth0middlewarepackage\Exceptions\AuthenticationException;
use willfd\auth0middlewarepackage\Exceptions\ConfigurationException;
use willfd\auth0middlewarepackage\Exceptions\TokenConfigurationException;
use willfd\auth0middlewarepackage\Services\AuthenticationService;

class Auth0AuthenticateTMFMiddleware
{
    protected array $errorResponseHeaders;
    public function __construct(
        protected Auth0Service $auth0Service,
        protected AuthenticationService $authenticationService,
        protected array $adminScopes,
        protected ?SdkConfiguration $sdkConfiguration,
        protected LoggerInterface $logger
    ) {
        $this->errorResponseHeaders = ['content-type' => 'application/json'];
    }

    public function handle(Request $request, Closure $next, string $requiredScope){

        try{
            $request = $this->authenticationService->authenticateScopesAndBuyer($request, $this->sdkConfiguration, $requiredScope, $this->adminScopes);
            $request = $this->auth0Service->authenticateAgainstAuth0Models($request);
            return $next($request);
        }
        catch(AuthenticationException $e){
            return new Response(
                "Authentication Fail - failed authentication",
                403,
                $this->errorResponseHeaders
            );
        } catch (ConfigurationException $e) {
            return new Response(
                "Authentication Fail - Config internal ERROR",
                500,
                $this->errorResponseHeaders
            );
        } catch (TokenConfigurationException $e) {
            return new Response(
                "Authentication Fail - invalid request",
                401,
                $this->errorResponseHeaders
            );
        } catch (InvalidTokenException $e) {
            return new Response(
                "Authentication Fail - failed Auth0 authentication",
                403,
                $this->errorResponseHeaders
            );
        } catch (Auth0DataException $e) {
            return new Response(
                "Authentication Fail - BuyerId not found",
                404,
                $this->errorResponseHeaders
            );
        }
    }
}