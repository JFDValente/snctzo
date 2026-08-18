<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$raizDaAplicacao = dirname(__DIR__, 2).'/snctzo-app/apps/web';

if (! is_file($raizDaAplicacao.'/vendor/autoload.php')) {
    http_response_code(503);

    exit('Aplicação temporariamente indisponível.');
}

if (file_exists($manutencao = $raizDaAplicacao.'/storage/framework/maintenance.php')) {
    require $manutencao;
}

require $raizDaAplicacao.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $raizDaAplicacao.'/bootstrap/app.php';
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
