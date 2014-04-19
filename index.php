<?php

$app = require __DIR__.'/bootstrap/bootstrap.php';

if ($app['debug'])
{
    $app->run();
}
else
{
    $app['http_cache']->run();
}
