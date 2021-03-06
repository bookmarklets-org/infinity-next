<?php

namespace App\Http\Middleware;

use Config;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession as BaseStartSession;
use Illuminate\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;

class StartSession extends BaseStartSession
{
    protected $shouldPassThrough = false;

    /**
     * Patterns matching URIs that do not require persistent sessions.
     *
     * @var array
     */
    protected $except = [
        '*/file/*',
    ];

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {

        foreach ($this->except as $except)
        {
            if ($except !== '/')
            {
                $except = trim($except, '/');
            }

            if ($request->is($except))
            {
                return $this->shouldPassThrough = true;
            }
        }

        return $this->shouldPassThrough = false;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->shouldPassThrough($request))
        {
            return parent::handle($request, $next);
        }

        if (!$request->cookie())
        {
            Config::set('session.driver', 'array');
        }

        return parent::handle($request, $next);
    }

    /**
     * Add the session cookie to the application response.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  \Illuminate\Session\SessionInterface  $session
     * @return void
     */
    protected function addCookieToResponse(Response $response, SessionInterface $session)
    {
        if (!$this->shouldPassThrough)
        {
            parent::addCookieToResponse($response, $session);
        }
    }
}
