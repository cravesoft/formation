<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;

class HomeControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        $em = $app['orm.em'];

        $controllers->get('/', function () use ($app, $em) {
            $user = $app->user();
            return $app->renderView('dashboard/home.html.twig', array(
                'user' => $user,
            ));
        })
        ->bind('home');

        return $controllers;
    }
}
