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
    public function bootstrap(): void
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

    protected function bootstrapCli(): void
    {
        $this->addPlugin('Bake');
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            ->add(new ErrorHandlerMiddleware(Configure::read('Error')))
            ->add(new AssetMiddleware(['cacheTime' => Configure::read('Asset.cacheTime') ?? '+1 year']))
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware());

        return $middlewareQueue;
    }

    public function routes(RouteBuilder $routes): void
    {
        parent::routes($routes);
        require CONFIG . 'routes.php';
    }

    public function console(CommandCollection $commands): CommandCollection
    {
        $commands = parent::console($commands);
        return $commands;
    }
}
