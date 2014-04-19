<?php

use Silex\Application;

class CustomApplication extends Application
{
    use Application\TwigTrait;
    use Application\SecurityTrait;
    use Application\FormTrait;
    use Application\UrlGeneratorTrait;
    use Application\SwiftmailerTrait;
    use Application\MonologTrait;
    use Application\TranslationTrait;
}

$app = new CustomApplication(); 

$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../config/formation.yml'));
$app['debug'] = $app['config']['formation']['debug'];
