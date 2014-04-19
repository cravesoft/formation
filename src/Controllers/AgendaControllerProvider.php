<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;

class AgendaControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        $em = $app['orm.em'];

        $controllers->get('/', function() use($app) {
            $user = $app->user();
            $body = $app->renderView('dashboard/agenda.html.twig', array(
                'user' => $user,
                'groups' => array(),
            ));
            return new Response($body, 200, array('Cache-Control' => 's-maxage=3600, private'));
        })
        ->bind('agenda');

        return $controllers;
    }
}
