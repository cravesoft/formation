<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;

class UserControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        $em = $app['orm.em'];

        $controllers->get('/{user_id}', function($user_id) use($app, $em)
        {
            $user = $app->user();
            $body = $app->renderView('users/new.html.twig', array(
            ));
            return new Response($body, 200, array('Cache-Control' => 's-maxage=3600, private'));
        })
        ->assert('user_id', '\d+')
        ->bind('user');

        $controllers = $app['controllers_factory'];
        $em = $app['orm.em'];

        $controllers->get('/new', function() use($app) {
            $user = $app->user();
            $body = $app->renderView('users/new.html.twig', array(
            ));
            return new Response($body, 200, array('Cache-Control' => 's-maxage=3600, private'));
        })
        ->bind('new_user');

        return $controllers;
    }
}
