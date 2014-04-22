<?php

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

date_default_timezone_set('Europe/Paris');

if($app['config']['formation']['https'])
{
    $app['base_url'] = 'https://';
}
else
{
    $app['base_url'] = 'http://';
}
$app['base_url'] = $app['base_url'].$app['config']['formation']['host'].
        ':'.$app['config']['formation']['port'];

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../log/production.log',
));

$app->register(new Silex\Provider\SwiftmailerServiceProvider());

$app['swiftmailer.options'] = array(
    $app['config']['mailer']
);

$em = $app['orm.em'];

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback' => $app['config']['formation']['default_language'],
));

use Symfony\Component\Translation\Loader\YamlFileLoader;
$app['translator'] = $app->share($app->extend('translator', function ($translator, $app)
{
    $translator->addLoader('yaml', new YamlFileLoader());

    foreach ($app['config']['formation']['languages'] as $language)
    {
        $translator->addResource('yaml', __DIR__.'/../translations/'.$language.'.yml', $language);
    }

    return $translator;
}));

$app->register(new Nicl\Silex\MarkdownServiceProvider());

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../src/Views',
));

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new SilexAssetic\AsseticServiceProvider());

$app['assetic.path_to_web'] = __DIR__.'/..';
$app['assetic.options'] = array(
    'debug' => true,
    'auto_dump_assets' => true
);
$app['assetic.filter_manager'] = $app->share(
    $app->extend('assetic.filter_manager', function($fm, $app)
    {
        $fm->set('scss', new Assetic\Filter\Sass\ScssFilter(
            '/usr/local/bin/sass'
        ));
        $fm->set('yui_css', new Assetic\Filter\Yui\CssCompressorFilter(
            '/usr/share/yui-compressor/yui-compressor.jar'
        ));
        $fm->set('yui_js', new Assetic\Filter\Yui\JsCompressorFilter(
            '/usr/share/yui-compressor/yui-compressor.jar'
        ));

        return $fm;
    })
);
$app['assetic.asset_manager'] = $app->share(
    $app->extend('assetic.asset_manager', function($am, $app)
    {
        $am->set('styles', new Assetic\Asset\AssetCache(
            new Assetic\Asset\GlobAsset(
                __DIR__ . '/../assets/scss/*.scss',
                array(
                    $app['assetic.filter_manager']->get('scss'),
                    $app['assetic.filter_manager']->get('yui_css')
                )
            ),
            new Assetic\Cache\FilesystemCache(__DIR__ . '/../cache/assetic')
        ));
        $am->get('styles')->setTargetPath('assets/css/application.css');

        return $am;
    })
);

$app['twig'] = $app->share($app->extend('twig', function($twig, $app)
{
    $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use($app)
    {
        return sprintf($app['base_url'].'/assets/%s', ltrim($asset, '/'));
    }));

    return $twig;
}));

//$app->error(function (\Exception $e, $code) use ($app)
//{
//    $page = 404 == $code ? 'templates/404.twig' : 'templates/500.twig';
//    return new Response($app->render($page, array('code' => $code)), $code);
//});

use Symfony\Component\HttpFoundation\Request;

$app->mount('/home', new Controllers\HomeControllerProvider());
$app->mount('/trainings', new Controllers\TrainingControllerProvider());
$app->mount('/sessions', new Controllers\SessionControllerProvider());
$app->mount('/requests', new Controllers\RequestControllerProvider());
$app->mount('/agenda', new Controllers\AgendaControllerProvider());
$app->mount('/profile', new Controllers\ProfileControllerProvider());
$app->mount('/reservations', new Controllers\ReservationControllerProvider());
$app->mount('/admin', new Controllers\AdminControllerProvider());
$app->mount('/users', new Controllers\UserControllerProvider());

$app->get('/', function() use($app, $em)
{ 
    //return $app->redirect('/home');
    return $app->redirect('/trainings');
})
->bind('index');

$app->get('/login', function (Request $request) use ($app)
{
    return $app->render('layouts/login.html.twig', array(
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
    ));
})
->bind('login');

function getNumResults($em, $table, $subquery, $params)
{
    $qb = $em->createQueryBuilder();
    $qb->select('count(default.id)')
        ->from($table, 'default')
        ->where($qb->expr()->in('default.id', $subquery));
    $qb->setParameters($params);
    return $qb->getQuery()->getSingleScalarResult();
}
