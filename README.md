## Disclaimer

**This documentation is in progress, its updated every day.**

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
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

/*------------------------------------------
| Instantiating the application             |
|----------------------------------------- */

$app = Quill\Factory\QuillFactory::make();


/*------------------------------------------
| Do anything before handling the request   |
|----------------------------------------- */

$app->get('/', function (RequestInterface $req, ResponseInterface $res) {
    return $res->json(['execution_time' => microtime(true) - QUILL_START]);
});

/*------------------------------------------
| Handling the request and sending response |
|------------------------------------------*/

$app->up();
```