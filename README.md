## Disclaimer

**This documentation is in progress and is updated every day.**

**This is a development version of the Framework, use at your own risk.**

## Quill
A simple way to make lightweight PHP APIs

## Installation 
The recommended way to install Quill is through 
[Composer](https://getcomposer.org/).

```bash
composer require nonaje/quill
```

## Basic Usage
We can use Quill with a syntax similar to [Express.Js](https://expressjs.com/)

```php
<?php

declare(strict_types=1);

use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;

require_once __DIR__ . '/vendor/autoload.php';

define('QUILL_START', microtime(true));

$app = quill();

$router = $app->router();

$router->get('/', function (RequestInterface $req, ResponseInterface $res): ResponseInterface {
    return $res->json(['execution_time' => microtime(true) - QUILL_START]);
});

$app->up();
```

It is not necessary to specify the data types, it's a developer's decision.

At Quill, we consider it a good practice.

## Knowing Features

Today Quill has several features that facilitate API development tasks,
and we want to add more in the near future.

### Router

It's possible to map routes with the following HTTP Methods

```php
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;

$router->get('/', fn (RequestInterface $req, ResponseInterface $res): ResponseInterface => $res->json([
    'HTTP Method' => $req->psrRequest()->getMethod()
]));

$router->post('/', fn (RequestInterface $req, ResponseInterface $res): ResponseInterface => $res->json([
    'HTTP Method' => $req->psrRequest()->getMethod()
]));

$router->put('/', fn (RequestInterface $req, ResponseInterface $res): ResponseInterface => $res->json([
    'HTTP Method' => $req->psrRequest()->getMethod()
]));

$router->patch('/', fn (RequestInterface $req, ResponseInterface $res): ResponseInterface => $res->json([
    'HTTP Method' => $req->psrRequest()->getMethod()
]));

$router->delete('/', fn (RequestInterface $req, ResponseInterface $res): ResponseInterface => $res->json([
    'HTTP Method' => $req->psrRequest()->getMethod()
]));
```

### Recursive Groups

In addition, it is possible to create groups of routes.

As you will see below, it is also possible to create recursive groups.

```php
use Quill\Contracts\Router\RouterInterface;
use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;

$router->group('/api/', function (RouterInterface $router): void {

    $router->get('/foo', fn (RequestInterface $req, ResponseInterface $res): ResponseInterface => $res->json([
        'URI' => $req->psrRequest()->getUri()->getPath()
    ]));

    $router->group('/examples', function (RouterInterface $router) {

        $router->get('/group-inside-group', fn(RequestInterface $req, ResponseInterface $res): ResponseInterface => $res->json([
            'URI' => $req->psrRequest()->getUri()->getPath()
        ]));
    });
});
```

### Isolated Route Files

For more convenience and easier readability of the code you can separate
your groups / routes into separate files as you will see below

With the help of the global function 'path()'
it is easier to indicate where the 'examples.php' routes file is located.

```php
use Quill\Contracts\Router\RouterInterface;

$router->group('/api/', function (RouterInterface $router): void {

    $examplesRoutes = path()->routeFile('examples.php');
    $router->loadRoutesFrom($examplesRoutes);
});
```

The file '/routes/examples.php' looks like this

```php
<?php

use Quill\Contracts\Request\RequestInterface;
use Quill\Contracts\Response\ResponseInterface;
use Quill\Contracts\Router\RouterInterface;

return function (RouterInterface $router): void {
    $router->group('/examples', function (RouterInterface $router) {

        $router->get('/group-inside-group',
            fn(RequestInterface $req, ResponseInterface $res): ResponseInterface => $res->json([
                'URI' => $req->psrRequest()->getUri()->getPath()
            ])
        );

        $router->get('/another-route-inside-group',
            fn(RequestInterface $req, ResponseInterface $res): ResponseInterface => $res->json([
                'URI' => $req->psrRequest()->getUri()->getPath()
            ])
        );
    });
};
```
