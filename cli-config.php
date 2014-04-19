<?php

// replace with file to your own project bootstrap
$loader = require __DIR__.'/vendor/autoload.php';

require_once __DIR__.'/bootstrap/config.php';
require_once __DIR__.'/bootstrap/database.php';

// replace with mechanism to retrieve EntityManager in your app
$em = $app['orm.em'];

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));

return $helperSet;
