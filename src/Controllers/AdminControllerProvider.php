<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AdminControllerProvider implements ControllerProviderInterface
{
    const NUM_USERS_PER_PAGE = 10;

    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        $em = $app['orm.em'];

        $controllers->get('/', function() use($app) {
            $user = $app->user();
            $body = $app->renderView('admin/home.html.twig', array(
                'user' => $user,
            ));
            return new Response($body, 200, array('Cache-Control' => 's-maxage=3600, private'));
        })
        ->bind('admin');

        $controllers->get('/users', function(Request $request) use($app, $em)
        {
            $user = $app->user();
            $roles = $request->get('roles', null);
            $page = $request->get('page', 1);

            /* Get number of pages in list of users */
            $params = array();
            $qb = $em->createQueryBuilder();
            $qb->select('u.id')
                ->from('Entities\User', 'u');
            if(null !== $roles)
            {
                $qb->where('u.roles = :roles');
                $params['roles'] = $roles;
            }
            $num_pages = intval(ceil(floatval(getNumResults($em,
                    'Entities\User', $qb->getDQL(),
                    $params)) / AdminControllerProvider::NUM_USERS_PER_PAGE));

            /* Reset page number if greater than number of pages */
            if($page > $num_pages)
            {
                $page = 1;
            }

            // get list of users
            $qb = $em->createQueryBuilder();
            $qb->select(array('u'))
                ->from('Entities\User', 'u')
                ->setFirstResult(($page-1)*AdminControllerProvider::NUM_USERS_PER_PAGE)
                ->setMaxResults(AdminControllerProvider::NUM_USERS_PER_PAGE)
                ->orderBy('u.surname');
            if(null !== $roles)
            {
                $qb->where('u.roles = :roles');
            }
            $qb->setParameters($params);
            $query = $qb->getQuery();
            $users = new Paginator($query, $fetchJoinCollection = true);

            return $app->render('admin/users.html.twig', array(
                'users' => $users,
                'roles' => $roles,
                'current_page' => $page,
                'num_pages' => $num_pages,
            ));
        })
        ->bind('users');

        $controllers->get('/groups', function() use($app, $em)
        {
            $group = $app->user();
            return $app->render('admin/groups.html.twig', array(
            ));
        })
        ->bind('groups');

        $controllers->get('/rooms', function() use($app, $em)
        {
            $room = $app->user();
            return $app->render('admin/rooms.html.twig', array(
            ));
        })
        ->bind('rooms');

        return $controllers;
    }
}
