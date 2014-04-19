<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Entities\SessionRequest;
use Doctrine\ORM\Tools\Pagination\Paginator;

class RequestControllerProvider implements ControllerProviderInterface
{
    const NUM_REQUESTS_PER_PAGE = 10;

    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        $em = $app['orm.em'];

        $controllers->get('/', function(Request $request) use($app, $em)
        { 
            $user = $app->user();
            $member = $request->get('member', null);
            $group = $request->get('group', null);
            $sort = $request->get('sort', 'name');
            $scope = $request->get('scope', 'all');
            $state = $request->get('state', 'all');
            $page = $request->get('page', 1);
            $num_requests = array(
                'created' => 0,
                'accepted'  => 0,
                'refused'   => 0,
                'all'       => 0,
            );
            $today = new \DateTime();
        
            /* Get number of sessions */
            $qb = $em->createQueryBuilder();
            $qb->select('sr.id')
                ->from('Entities\SessionRequest', 'sr')
                ->innerjoin('sr.user', 'u', 'with', 'u.manager = :manager');
            $params = array('manager' => $user);
            $num_requests['all'] = getNumResults($em, 'Entities\SessionRequest',
                    $qb->getDQL(), $params);
        
            $qb = $em->createQueryBuilder();
            $qb->select('sr.id')
                ->from('Entities\SessionRequest', 'sr')
                ->where('sr.status = :status')
                ->innerjoin('sr.user', 'u', 'with', 'u.manager = :manager');
            $params = array(
                    'manager' => $user,
                    'status' => SessionRequest::STATUS_CREATED);
            $num_requests['created'] = getNumResults($em,
                    'Entities\SessionRequest', $qb->getDQL(), $params);
            $params = array(
                    'manager' => $user,
                    'status' => SessionRequest::STATUS_ACCEPTED);
            $num_requests['accepted'] = getNumResults($em,
                    'Entities\SessionRequest', $qb->getDQL(), $params);
            $params = array(
                    'manager' => $user,
                    'status' => SessionRequest::STATUS_REFUSED);
            $num_requests['refused'] = getNumResults($em,
                    'Entities\SessionRequest', $qb->getDQL(), $params);
        
            /* Get list of groups */
            $qb = $em->createQueryBuilder();
            $qb->select(array('g'))
                ->from('Entities\TrainingGroup', 'g');
            $query = $qb->getQuery();
            $groups = $query->getArrayResult();
        
            /* Get team members */
            $qb = $em->createQueryBuilder();
            $qb->select(array('u', 'r'))
                ->from('Entities\User', 'u')
                ->leftjoin('u.team', 't')
                ->leftjoin('u.requests', 'r')
                ->where('u.manager = :manager');
            $qb->setParameter('manager', $user);
            $query = $qb->getQuery();
            $members = new Paginator($query, $fetchJoinCollection = true);
        
            /* Get number of pages in list of requests */
            $params = array('manager' => $user);
            $qb = $em->createQueryBuilder();
            $qb->select('sr.id')
                ->from('Entities\SessionRequest', 'sr')
                ->innerjoin('sr.session', 's')
                ->innerjoin('sr.user', 'u', 'with', 'u.manager = :manager');
            if('all' !== $scope) {
                $qb->where('sr.status = :status');
                if('accepted' === $scope) {
                    $params['status'] = SessionRequest::STATUS_ACCEPTED;
                } elseif('refused' === $scope) {
                    $params['status'] = SessionRequest::STATUS_REFUSED;
                } else {
                    $params['status'] = SessionRequest::STATUS_CREATED;
                }
            }
            if(null !== $member) {
                $qb->andWhere('u.id = :member');
                $params['member'] = $member;
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
            $num_pages = intval(ceil(floatval(getNumResults($em,
                    'Entities\SessionRequest', $qb->getDQL(),
                    $params)) / RequestControllerProvider::NUM_REQUESTS_PER_PAGE));
        
            /* Reset page number if greater than number of pages */
            if($page > $num_pages)
            {
                $page = 1;
            }
        
            /* Get list of requests */
            $params = array('manager' => $user);
            $qb = $em->createQueryBuilder();
            $qb->select('sr', 's', 't', 'g', 'i', 'r', 'u')
                ->from('Entities\SessionRequest', 'sr')
                ->innerjoin('sr.session', 's')
                ->leftjoin('s.training', 't')
                ->leftjoin('t.instructors', 'i')
                ->innerjoin('sr.user', 'u', 'with', 'u.manager = :manager')
                ->setFirstResult(($page-1)*RequestControllerProvider::NUM_REQUESTS_PER_PAGE)
                ->setMaxResults(RequestControllerProvider::NUM_REQUESTS_PER_PAGE);
            if('all' !== $scope) {
                $qb->where('sr.status = :status');
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
                $qb->innerjoin('t.group', 'g');
            }
            if(null !== $member) {
                $qb->andWhere('u.id = :member');
                $params['member'] = $member;
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
            $qb->setParameters($params);
            $query = $qb->getQuery();
            $requests = new Paginator($query, $fetchJoinCollection = true);
        
            return $app->render('dashboard/requests.html.twig', array(
                'user' => $user,
                'sort' => $sort,
                'scope' => $scope,
                'state' => $state,
                'requests' => $requests,
                'group' => $group,
                'groups' => $groups,
                'member' => $member,
                'members' => $members,
                'num_requests' => $num_requests,
                'current_page' => $page,
                'num_pages' => $num_pages,
            ));
        })
        ->bind('requests');

        $controllers->post('/new', function(Request $request) use($app, $em)
        {
            $user = $app->user();
            $session_id = $request->get('session_id', false);
            if($session_id)
            {
                $session = $em->find('Entities\Session', $session_id);
                $request = new SessionRequest();
                $request->setUser($user);
                $request->setSession($session);
                $em->persist($request);
                $em->flush();
        
                $app['monolog']->addInfo(sprintf("User '%s' requested a training.",
                        $user->getUsername()));
                $manager = $user->getManager();
                if(null !== $manager)
                {
                    $message = \Swift_Message::newInstance()
                        ->setSubject(sprintf("[%s] Training request awaiting for your approval",
                                $app['base_url']))
                        ->setFrom(array($app['config']['formation']['email_from']))
                        ->setTo($manager->getEmail())
                        ->setBody($app->render('emails/request-create.html.twig', array(
                                'base_url' => $app['base_url'],
                                'user' => $user,
                                'manager' => $manager)))
                        ->setContentType("text/html");
        
                    $app['mailer']->send($message);
                }
                return $app->json("OK");
            }
            else
            {
                return $app->json("KO");
            }
        })
        ->bind('new_request_check');

        $controllers->post('/edit', function(Request $request) use($app, $em)
        { 
            $user = $app->user();
            $request_id = $request->get('request_id', false);
            $action = $request->get('action', false);
            if($request_id && $action)
            {
                $request = $em->find('Entities\SessionRequest', $request_id);
                if('refuse' === $action)
                {
                    $status = SessionRequest::STATUS_REFUSED;
                    $app['monolog']->addInfo(sprintf("User '%s' refused a training request.",
                            $user->getUsername()));
                    $request->setStatus($status);
                    $em->flush();
                    $message = \Swift_Message::newInstance()
                        ->setSubject(sprintf("[%s] Your training request has been refused",
                                $app['base_url']))
                        ->setFrom(array($app['config']['formation']['email_from']))
                        ->setTo($request->getUser()->getEmail())
                        ->setBody($app->render('emails/request-refuse.html.twig', array(
                                'base_url' => $app['base_url'],
                                'user' => $request->getUser(),
                                'manager' => $user)))
                        ->setContentType("text/html");
                    $app['mailer']->send($message);
                }
                elseif('accept' === $action)
                {
                    $status = SessionRequest::STATUS_ACCEPTED;
                    $request->setStatus($status);
                    $em->flush();
                    $app['monolog']->addInfo(sprintf("User '%s' accepted a training request.",
                            $user->getUsername()));
                    $message = \Swift_Message::newInstance()
                        ->setSubject(sprintf("[%s] Your training request has been accepted",
                                $app['base_url']))
                        ->setFrom(array($app['config']['formation']['email_from']))
                        ->setTo($request->getUser()->getEmail())
                        ->setBody($app->render('emails/request-accept.html.twig', array(
                                'base_url' => $app['base_url'],
                                'user' => $request->getUser(),
                                'manager' => $user)))
                        ->setContentType("text/html");
                    return 
                        $app->render('emails/request-accept.html.twig', array(
                                'base_url' => $app['base_url'],
                                'user' => $request->getUser(),
                                'manager' => $user));
                    $app['mailer']->send($message);
                }

                return $app->json(array('action' => $action));
            }
            else
            {
                return $app->json(null);
            }
        })
        ->bind('edit_request_check');

        return $controllers;
    }
}

