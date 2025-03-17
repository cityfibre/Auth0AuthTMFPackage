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
            return $this->generateErrorResponse(403, "Authentication Fail - failed authentication", $e->getMessage(), 403);
        } catch (ConfigurationException $e) {
            return $this->generateErrorResponse(500, "Internal ERROR - authentication config error", $e->getMessage(), 500);
        } catch (TokenConfigurationException $e) {
            return $this->generateErrorResponse(401, "Authentication Fail - invalid request auth", $e->getMessage(), 401);
        } catch (InvalidTokenException $e) {
            return $this->generateErrorResponse(403, "Authentication Fail - failed Auth0 authentication", $e->getMessage(), 403);
        } catch (Auth0DataException $e) {
            return $this->generateErrorResponse(404, "Authentication Fail - BuyerId not found", $e->getMessage(), 404);
        }
    }

    protected function generateErrorResponse(string | int $code, string $reason, ?string $message, int $status): Response
    {
        $coreResponseBody = [
            "@type" => "Error",
            "code" => $code,
            "reason" => $reason,
        ];

        if( !is_null($message) ){
            $coreResponseBody["message"] = $message;
        }

        if( $status != $code ){
            $coreResponseBody["status"] = $status;
        }

        return new Response($coreResponseBody, $status, $this->errorResponseHeaders);
    }
}