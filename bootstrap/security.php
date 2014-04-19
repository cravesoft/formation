<?php

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'login' => array(
            'pattern' => '^/login$',
        ),
        'secured' => array(
            'pattern' => '^.*$',
            'form' => array('login_path' => '/login', 'check_path' => '/login_check'),
            'logout' => array('logout_path' => '/logout'),
            'remember_me' => array(
                    'key' => '362e65591e82e2181134be9e7ce49943'
            ),
            'users' => $app->share(function () use ($app)
            {
                return new Users\UserProvider($app['orm.em']);
            }),
        ),
    )
));

$app->register(new Silex\Provider\RememberMeServiceProvider());

$app['security.role_hierarchy'] = array(
    'ROLE_ADMIN' => array('ROLE_ADVANCED_USER', 'ROLE_ALLOWED_TO_SWITCH'),
    'ROLE_ADVANCED_USER' => array('ROLE_USER', 'ROLE_ALLOWED_TO_SWITCH'),
);

$app['security.access_rules'] = array(
    array('^.*$', 'ROLE_USER', 'http'),
);

//use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
//$app['security.encoder.digest'] = $app->share(function ($app)
//{
//    // use the sha1 algorithm
//    // don't base64 encode the password
//    // use only 1 iteration
//    return new MessageDigestPasswordEncoder('sha512', false, 1);
//});
