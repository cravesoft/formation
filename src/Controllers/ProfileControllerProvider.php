<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class ProfileControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        $em = $app['orm.em'];

        $controllers->get('/', function() use($app, $em)
        {
            $user = $app->user();
            return $app->render('profiles/show.html.twig', array(
                'user' => $user,
            ));
        })
        ->bind('profile');

        $controllers->post('/', function(Request $request) use($app, $em)
        {
            $user = $app->user();
            $data = $request->get('user', false);
            if($data) {
                $surname = $data['surname'];
                $givenname = $data['givenname'];
                $email = $data['email'];
                $website = $data['website_url'];
                $bio = $data['bio'];

                $user->setSurname($surname);
                $user->setGivenname($givenname);
                $user->setEmail($email);
                $user->setWebsite($website);
                $user->setBio($bio);
                $em->flush();
            }
            return $app->redirect('/profile');
        })
        ->bind('check_profile');

        $controllers->get('/notifications', function() use($app, $em)
        {
            $user = $app->user();
            return $app->render('profiles/notifications.html.twig', array(
            ));
        })
        ->bind('notifications');

        $controllers->get('/history', function() use($app, $em)
        {
            $user = $app->user();
            return $app->render('profiles/history.html.twig', array(
            ));
        })
        ->bind('history');

        return $controllers;
    }
}
