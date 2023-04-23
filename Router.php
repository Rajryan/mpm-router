<?php
namespace Mpm\FakeRouter;

class Router
{
    private $routes = [];

    public function get($uri, $handler)
    {
        $this->routes['GET'][$uri] = $handler;
    }

    public function post($uri, $handler)
    {
        $this->routes['POST'][$uri] = $handler;
    }

    public function put($uri, $handler)
    {
        $this->routes['PUT'][$uri] = $handler;
    }

    public function delete($uri, $handler)
    {
        $this->routes['DELETE'][$uri] = $handler;
    }

    public function match($requestUri, $requestMethod)
    {
        foreach ($this->routes[$requestMethod] as $uri => $handler) {
            $uri = preg_replace('/\//', '\\/', $uri);
            $uri = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[^\/]+)', $uri);
            $uri = '/^' . $uri . '$/i';

            if (preg_match($uri, $requestUri, $matches)) {
                return [
                    'handler' => $handler,
                    'params' => array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY)
                ];
            }
        }

        return null;
    }

    public function run($requestUri, $requestMethod)
    {
        $route = $this->match($requestUri, $requestMethod);

        if (!$route) {
            // handle 404 error
            echo '404 Not Found';
            exit();
        }

        $handler = $route['handler'];
        $params = $route['params'];

        if (is_string($handler)) {
            $handler    = explode('@', $handler);
            $controller = new $handler[0];
            $method  = $handler[1];
            call_user_func_array([$controller, $method], $params);
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $params);
        }
    }
}
