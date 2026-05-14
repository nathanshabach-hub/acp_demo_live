<?php
declare(strict_types=1);

namespace App;

use Cake\Console\CommandCollection;
use Cake\Core\Configure;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\RouteBuilder;

class Application extends BaseApplication
{
    public function bootstrap()
    {
        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        }

        $this->addPlugin('Migrations');

        if (Configure::read('debug')) {
            $this->addPlugin('DebugKit');
            $this->addPlugin('AkkaFacebook', ['autoload' => true]);
        }
    }

    protected function bootstrapCli()
    {
        $this->addPlugin('Bake');
    }

    public function middleware($middleware)
    {
        $middleware
            ->add(new ErrorHandlerMiddleware())
            ->add(new AssetMiddleware(['cacheTime' => Configure::read('Asset.cacheTime') ?? '+1 year']))
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware());

        return $middleware;
    }

    public function routes($routes)
    {
        parent::routes($routes);
        $closure = require CONFIG . 'routes.php';
        if (is_callable($closure)) {
            $closure($routes);
        }
    }

    public function console($commands)
    {
        $commands = parent::console($commands);
        return $commands;
    }
}
