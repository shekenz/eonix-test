<?php

namespace API;

use FastRoute;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher;

/**
 * Router
 */
class Router
{
    private $dispatcher;
    private $uri;
    
    public function __construct(array $routes)
    {
        $this->uri = $this->stripQuery($_SERVER['REQUEST_URI']);
        $this->dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) use ($routes) {
            foreach($routes as $route) {
                $r->addRoute(...$route);
            }
        });
    }

    protected function stripQuery(string $uri): string
    {
        if(false !== $position = strpos($uri, '?')) {
            $uri = substr($uri, 0, $position);
        }

        return rawurldecode($uri);
    }

    public function dispatch(): void
    {
        $route = $this->dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $this->uri);
        switch($route[0])
        {
            case Dispatcher::NOT_FOUND: echo '404'; break;
            case Dispatcher::METHOD_NOT_ALLOWED: echo '405'; break;
            case Dispatcher::FOUND: $route[1]($route[2]); break;
        }
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}