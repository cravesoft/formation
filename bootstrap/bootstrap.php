<?php

$loader = require __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/config.php';
require_once __DIR__.'/database.php';
require_once __DIR__.'/security.php';
require_once __DIR__.'/ldap.php';
require_once __DIR__.'/cache.php';
require_once __DIR__.'/general.php';

return $app;
