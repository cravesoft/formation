<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Entities\SessionRequest;
use Entities\Session;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SessionControllerProvider implements ControllerProviderInterface
{
    const NUM_SESSIONS_PER_PAGE = 10;

    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        $em = $app['orm.em'];

        $controllers->get('/', function(Request $request) use($app, $em)
        { 
            $user = $app->user();
            $group = $request->get('group', null);
            $sort = $request->get('sort', 'name');
            $scope = $request->get('scope', 'all');
            $state = $request->get('state', 'all');
            $page = $request->get('page', 1);
            $num_sessions = array(
                'created' => 0,
                'accepted'  => 0,
                'refused'   => 0,
                'all'       => 0,
            );
            $today = new \DateTime();

            /* Get number of sessions */
            $repository = $em->getRepository('Entities\Session');
            //$qb = $repository->createQueryBuilder('s')
            //    ->innerjoin('s.requests', 'sr')
            //    ->innerjoin('sr.user', 'u', 'with', 'u.id = :user');
            //$params = array('user' => $user);
            //$num_sessions['all'] = getNumResults($em, 'Entities\Session', $qb->getDQL(), $params);
            $num_sessions['all'] = $em->getRepository('Entities\Session')->countByUser($user);

            $qb = $repository->createQueryBuilder('s')
                ->innerjoin('s.requests', 'sr', 'with', 'sr.status = :status')
                ->innerjoin('sr.user', 'u', 'with', 'u.id = :user');
            $params = array('user' => $user, 'status' => SessionRequest::STATUS_CREATED);
            $num_sessions['created'] = getNumResults($em, 'Entities\Session', $qb->getDQL(), $params);
            $params = array('user' => $user, 'status' => SessionRequest::STATUS_ACCEPTED);
            $num_sessions['accepted'] = getNumResults($em, 'Entities\Session', $qb->getDQL(), $params);
            $params = array('user' => $user, 'status' => SessionRequest::STATUS_REFUSED);
            $num_sessions['refused'] = getNumResults($em, 'Entities\Session', $qb->getDQL(), $params);

            /* Get number of pages in list of sessions */
            $params = array('user' => $user);
            $qb = $repository->createQueryBuilder('s');
            if('all' === $scope) {
                $qb->innerjoin('s.requests', 'sr')
                    ->innerjoin('sr.user', 'u', 'with', 'u.id = :user');
            } else {
                $qb->innerjoin('s.requests', 'sr', 'with', 'sr.status = :status')
                    ->innerjoin('sr.user', 'u', 'with', 'u.id = :user');
                if('accepted' === $scope) {
                    $params['status'] = SessionRequest::STATUS_ACCEPTED;
                } elseif('refused' === $scope) {
                    $params['status'] = SessionRequest::STATUS_REFUSED;
                } else {
                    $params['status'] = SessionRequest::STATUS_CREATED;
                }
            }
            if(null !== $group) {
                $qb->innerjoin('s.training', 't')
                    ->innerjoin('t.group', 'g', 'with', 'g.name = :group');
                $params['group'] = $group;
            }
            if('past' === $state) {
                $qb->innerjoin('s.reservations', 'r', 'with', 'r.endDate < :date');
                $params['date'] = $today;
            } elseif('incoming' === $state) {
                $qb->innerjoin('s.reservations', 'r', 'with', 'r.startDate >= :date');
                $params['date'] = $today;
            }
            $num_pages = intval(ceil(floatval(getNumResults($em, 'Entities\Session', $qb->getDQL(), $params)) / SessionControllerProvider::NUM_SESSIONS_PER_PAGE));

            /* Reset page number if greater than number of pages */
            if($page > $num_pages)
            {
                $page = 1;
            }

            /* Get list of sessions */
            $sessions = array();
            $params = array('user' => $user);
            $qb = $em->createQueryBuilder();
            $select = array('s', 't', 'g', 'r', 'i', 'sr');
            $qb->from('Entities\Session', 's')
                ->leftjoin('s.training', 't')
                ->leftjoin('t.instructors', 'i')
                ->setFirstResult(($page-1)*SessionControllerProvider::NUM_SESSIONS_PER_PAGE)
                ->setMaxResults(SessionControllerProvider::NUM_SESSIONS_PER_PAGE);
            if('all' === $scope) {
                $qb->innerjoin('s.requests', 'sr')
                    ->innerjoin('sr.user', 'uu', 'with', 'uu.id = :user');
            } else {
                $qb->innerjoin('s.requests', 'sr', 'with', 'sr.status = :status')
                    ->innerjoin('sr.user', 'uu', 'with', 'uu.id = :user');
                if('accepted' === $scope) {
                    $params['status'] = SessionRequest::STATUS_ACCEPTED;
                } elseif('refused' === $scope) {
                    $params['status'] = SessionRequest::STATUS_REFUSED;
                } else {
                    $params['status'] = SessionRequest::STATUS_CREATED;
                }
            }
            if(null !== $group) {
                $qb->innerjoin('t.group', 'g', 'with', 'g.name = :group');
                $params['group'] = $group;
            } else {
                $qb->leftjoin('t.group', 'g');
            }
            if('all' === $state) {
                $qb->innerjoin('s.reservations', 'r');
            } else {
                $params['date'] = $today;
                if('past' === $state) {
                    $qb->innerjoin('s.reservations', 'r', 'with', 'r.endDate < :date');
                } else {
                    $qb->innerjoin('s.reservations', 'r', 'with', 'r.startDate >= :date');
                }
            }
            if('ascending' === $sort) {
                $qb->orderBy('r.startDate', 'asc');
            } elseif('descending' === $sort) {
                $qb->orderBy('r.startDate', 'desc');
            } else {
                $qb->orderBy('t.title');
            }
            $qb->select($select);
            $qb->setParameters($params);
            $query = $qb->getQuery();
            $sessions = new Paginator($query, $fetchJoinCollection = true);

            /* Get list of groups */
            $qb = $em->getRepository('Entities\TrainingGroup')->createQueryBuilder('g');
            $query = $qb->getQuery();
            $groups = $query->getArrayResult();

            $body = $app->renderView('dashboard/sessions.html.twig', array(
                        'sort' => $sort,
                        'user' => $user,
                        'scope' => $scope,
                        'state' => $state,
                        'sessions' => $sessions,
                        'group' => $group,
                        'groups' => $groups,
                        'num_sessions' => $num_sessions,
                        'current_page' => $page,
                        'num_pages' => $num_pages,
                        ));
            return new Response($body, 200, array('Cache-Control' => 's-maxage=3600, private'));
        })
        ->bind('sessions');

        $controllers->get('/new', function(Request $request) use($app, $em)
        {
            $user = $app->user();

            $training_id = $request->get('training_id', false);

            /* Get list of rooms */
            $qb = $em->createQueryBuilder();
            $qb->select(array('r'))
                ->from('Entities\Room', 'r');
            $query = $qb->getQuery();
            $rooms = $query->getArrayResult();

            if($training_id)
            {
                $training = $em->find('Entities\Training', $training_id);

                return $app->renderView('sessions/new.html.twig', array(
                    'user' => $user,
                    'training' => $training,
                    'rooms' => $rooms,
                ));
            }
        })
        ->bind('new_session');

        $controllers->get('/edit', function(Request $request) use($app, $em)
        {
            $user = $app->user();

            $session_id = $request->get('session_id', false);

            /* Get list of rooms */
            $qb = $em->createQueryBuilder();
            $qb->select(array('r'))
                ->from('Entities\Room', 'r');
            $query = $qb->getQuery();
            $rooms = $query->getArrayResult();

            if($session_id)
            {
                $session = $em->find('Entities\Session', $session_id);

                return $app->render('sessions/edit.html.twig', array(
                    'user' => $user,
                    'session' => $session,
                    'rooms' => $rooms,
                ));
            }
        })
        ->bind('edit_session');

        $controllers->post('/new', function(Request $request) use($app, $em)
        {
            $user = $app->user();
            $training_id = $request->get('training_id', false);
            $data = $request->get('session', false);
            if($data && $training_id)
            {
                $max_users = $data['max_users'];
                $comment = $data['comment'];
                $level = $data['level'];
                $reservations = $request->get('reservations', array());

                $training = $em->find('Entities\Training', $training_id);

                $session = new Session();
                $session->setMaxUsers($max_users)
                    ->setComment($comment)
                    ->setLevel($level)
                    ->setTraining($training);
                $em->persist($session);
                foreach($reservations as $reservation_id)
                {
                    $app['monolog']->addInfo(sprintf("Res '%d' .", $reservation_id));
                    $reservation = $em->find('Entities\Reservation', $reservation_id);
                    $reservation->setSession($session);
                }
                $em->flush();

                $app['monolog']->addInfo(sprintf("User '%s' created a new session.",
                        $user->getUsername()));

            }
            $url = $app->url('training', array(
                'training_id' => $training->getId()
            ));
            return $app->redirect($url);
        })
        ->bind('new_session_check');

        return $controllers;
    }

}
