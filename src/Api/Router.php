<?php

namespace Sparky7\Api;

use Sparky7\Api\Response\APIResponse;
use Sparky7\Error\Exception\ExBadRequest;
use Sparky7\Event\Emitter;
use Closure;

/**
 * Router class.
 */
class Router
{
    use Emitter;

    private $Request;

    private $callback;
    private $not_found;
    private $routes;

    /**
     * Construct method.
     */
    final public function __construct()
    {
        $this->Request = new Request();

        $this->callback = null;
        $this->not_found = null;
        $this->routes = [];
    }

    /**
     * Get method.
     *
     * @param string $key Parameter name
     *
     * @return string Parameter value
     */
    final public function __get($key)
    {
        return (isset($this->{$key})) ? $this->{$key} : null;
    }

    /**
     * Add a route.
     *
     * @param string $method  Method type
     * @param string $uri     End point
     * @param string $closure Callback
     */
    final private function addRoute($method, $uri, Closure $closure)
    {
        $uri = ltrim($uri, '/');
        $arr = explode('/', $uri);
        $size = count($arr);
        $cursor0 = &$this->routes;

        foreach ($arr as $key => $value) {
            // Adds the / to the routes
            $value = '/'.$value;

            if (($key + 1) < $size) {
                if (!isset(${'cursor'.$key}[$value])) {
                    ${'cursor'.$key}[$value] = [
                        'method' => [],
                        'children' => null,
                        'url' => $uri,
                    ];
                }

                ${'cursor'.($key + 1)} = &${'cursor'.$key}[$value]['children'];
            } else {
                ${'cursor'.$key}[$value]['method'][strtoupper($method)] = [
                    'closure' => $closure,
                    'uri' => $uri,
                ];
            }
        }

        $this->routes = $cursor0;
    }

    /**
     * Parses matched route.
     *
     * @param array $match Match array
     *
     * @return array Route
     */
    final private function parseRouteMatched(array $match)
    {
        $methods = [
            $this->Request->method,
            'ANY',
        ];

        // Pick exact matches first then variables
        usort($match, function ($first, $second) {
            return ($first['w'] > $second['w']) ? -1 : 1;
        });

        // Preflight response on method Options
        if (in_array(strtoupper($this->Request->method), ['HEAD', 'OPTIONS'])) {
            return $this->preFlight();
        }

        foreach ($match as $key => $value) {
            foreach ($methods as $method) {
                if (isset($value['method'][$method])) {
                    // Add variables
                    foreach (explode('/', $value['method'][$method]['uri']) as $key2 => $value2) {
                        if (strpos($value2, ':') === 0) {
                            $value2 = substr($value2, 1, strlen($value2));
                            $this->Request->setParam($value2, $this->Request->uri[$key2], 'URL');
                        }
                    }

                    return $value['method'][$method]['closure'];
                }
            }
        }

        return;
    }

    /**
     * Searches for a route.
     *
     * @return any False if route didn't match or a Route object if found
     */
    final private function searchRoute()
    {
        // Searches in custom routes
        $closure = null;
        $routes = $this->routes;

        if (count($this->Request->uri) === 0) {
            $request = [' '];
            $size = 1;
        } else {
            $request = $this->Request->uri;
            $size = count($this->Request->uri);
        }

        foreach ($request as $position => $req_dir) {
            $req_dir = trim($req_dir);

            $match = [];
            foreach ($routes as $route_dir => $route) {
                $route['w'] = 0;

                // Exact folder match
                if ('/'.$req_dir === $route_dir) {
                    $route['w'] = 1;
                    $match[] = $route;
                }

                // Variable match
                if (strpos($route_dir, '/:') === 0) {
                    $route['w'] = 0.5;
                    $match[] = $route;
                }

                // Wildcard
                if (strpos($route_dir, '/**') === 0) {
                    $route['w'] = 0.1;
                    $match[] = $route;

                    return $this->parseRouteMatched($match);
                }
            }

            // Overwrite routes before final position
            if (($position + 1) < $size) {
                $routes = [];

                // Dump all matches
                foreach ($match as $key => $value) {
                    if (isset($value['children'])) {
                        $routes = array_merge($routes, $value['children']);
                    }
                }
            } else {
                return $this->parseRouteMatched($match);
            }
        }
    }

    /**
     * Replace URL.
     *
     * @param string $replace Search for
     * @param string $with    Replace with
     */
    final public function replaceURL($replace, $with)
    {
        $this->Request->replaceURL($replace, $with);
    }

    /**
     * Mapped route to any.
     *
     * @param string $uri     End point
     * @param string $closure Callback
     */
    final public function any($uri, Closure $closure)
    {
        $this->addRoute(__FUNCTION__, $uri, $closure);
    }

    /**
     * Add a get route.
     *
     * @param string $uri     End point
     * @param string $closure Method callback
     */
    final public function get($uri, Closure $closure)
    {
        $this->addRoute(__FUNCTION__, $uri, $closure);
    }

    /**
     * Add a post route.
     *
     * @param string $uri     End point
     * @param string $closure Method callback
     */
    final public function post($uri, Closure $closure)
    {
        $this->addRoute(__FUNCTION__, $uri, $closure);
    }

    /**
     * Add a put route.
     *
     * @param string $uri     End point
     * @param string $closure Method callback
     */
    final public function put($uri, closure $closure)
    {
        $this->addRoute(__FUNCTION__, $uri, $closure);
    }

    /**
     * Add a delete route.
     *
     * @param string $uri     End point
     * @param string $closure Method callback
     */
    final public function delete($uri, Closure $closure)
    {
        $this->addRoute(__FUNCTION__, $uri, $closure);
    }

    /**
     * Add a not found route.
     *
     * @param string $closure Method callback
     */
    final public function notFound(Closure $closure)
    {
        $this->not_found = $closure;
    }

    /**
     * Pre flight response.
     *
     * @return APIResponse
     */
    final public function preFlight()
    {
        return function () {
            $APIResponse = new APIResponse();
            $APIResponse->code = 204;
            $APIResponse->rid = $this->Request->rid;

            return $APIResponse;
        };
    }

    /**
     * Executes matching route with requested url, and tries to executes its callback.
     */
    final public function run()
    {
        $this->emit('before.run', [$this->Request]);

        $closure = $this->searchRoute();

        // If closure is not callable use the not found closure
        if (!is_callable($closure) && is_callable($this->not_found)) {
            $closure = $this->not_found;
        }

        // Checks to see if closure is callable
        if (!is_callable($closure)) {
            throw new ExBadRequest('Route not found');
        }

        $Response = call_user_func_array($closure, [$this->Request]);

        if ($Response instanceof Response) {
            $this->emit('after.run', [$this->Request, $Response]);
        }

        $Response->sendHeaders();
        echo $Response->format();

        exit(1);
    }
}
