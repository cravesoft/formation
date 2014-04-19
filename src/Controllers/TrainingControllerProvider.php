<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Entities\Training;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TrainingControllerProvider implements ControllerProviderInterface
{
    const NUM_TRAININGS_PER_PAGE = 10;

    public function connect(Application $app)
    {
        // Creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        $em = $app['orm.em'];

        $controllers->get('/', function(Request $request) use($app, $em)
        {
            $user = $app->user();

            $group = $request->get('group', null);
            $status = $request->get('status', null);
            $page = $request->get('page', 1);

            $enabled = null;
            if($app['security']->isGranted('ROLE_ADVANCED_USER'))
            {
                if($status === 'enabled')
                {
                    $enabled = true;
                }
                elseif($status === 'disabled')
                {
                    $enabled = false;
                }
            }
            else
            {
                // Standard users can only see enabled trainings
                $enabled = true;
            }

            /* Get number of pages in list of trainings */
            $params = array();
            $qb = $em->createQueryBuilder();
            $qb->select('t.id')
                ->from('Entities\Training', 't');
            if(null !== $enabled)
            {
                $qb->where('t.isEnabled = :enabled');
                $params['enabled'] = $enabled;
            }
            if(null !== $group)
            {
                $qb->innerjoin('t.group', 'g', 'with', 'g.name = :group');
                $params['group'] = $group;
            }
            $num_pages = intval(ceil(floatval(getNumResults($em,
                    'Entities\Training', $qb->getDQL(),
                    $params)) / TrainingControllerProvider::NUM_TRAININGS_PER_PAGE));

            /* Reset page number if greater than number of pages */
            if($page > $num_pages)
            {
                $page = 1;
            }

            // get list of groups
            $qb = $em->createQueryBuilder();
            $qb->select(array('g'))
                ->from('Entities\TrainingGroup', 'g');
            $query = $qb->getQuery();
            $groups = $query->getArrayResult();

            // get list of trainings
            $qb = $em->createQueryBuilder();
            $qb->select(array('t', 'g', 'i'))
                ->from('Entities\Training', 't')
                ->leftjoin('t.instructors', 'i')
                ->setFirstResult(($page-1)*TrainingControllerProvider::NUM_TRAININGS_PER_PAGE)
                ->setMaxResults(TrainingControllerProvider::NUM_TRAININGS_PER_PAGE)
                ->orderBy('t.title');
            if(null !== $enabled)
            {
                $qb->where('t.isEnabled = :enabled');
            }
            if(null !== $group)
            {
                $qb->innerjoin('t.group', 'g', 'with', 'g.name = :group');
            }
            else
            {
                $qb->innerjoin('t.group', 'g');
            }
            $qb->setParameters($params);
            $query = $qb->getQuery();
            $trainings = new Paginator($query, $fetchJoinCollection = true);

            $body = $app->renderView('dashboard/trainings.html.twig', array(
                'user' => $user,
                'status' => $status,
                'group' => $group,
                'groups' => $groups,
                'trainings' => $trainings,
                'current_page' => $page,
                'num_pages' => $num_pages,
            ));
            return new Response($body, 200, array('Cache-Control' => 's-maxage=3600, private'));
        })
        ->bind('trainings');

        $controllers->get('/{training_id}', function($training_id) use($app, $em)
        {
            $today = new \DateTime();
            $user = $app->user();

            /* Get training info */
            $qb = $em->createQueryBuilder();
            $qb->select('t', 'i', 'g')
                ->from('Entities\Training', 't')
                ->leftjoin('t.instructors', 'i')
                ->innerjoin('t.group', 'g')
                ->where($qb->expr()->eq('t.id', ':training'));
            $qb->setParameter('training', $training_id);
            $query = $qb->getQuery();
            $training = $query->getSingleResult();

            /* Get list of incoming sessions */
            $qb = $em->createQueryBuilder();
            $qb->select('s', 'r', 'u', 'sr')
                ->from('Entities\Session', 's')
                ->innerjoin('s.reservations', 'r', 'with', 'r.startDate >= :date')
                ->leftjoin('s.requests', 'sr')
                ->leftjoin('sr.user', 'u', 'with', 'u.id = :user')
                ->orderBy('r.startDate', 'asc')
                ->where($qb->expr()->eq('s.training', ':training'));
            $qb->setParameters(array(
                    'training' => $training_id,
                    'date' => $today,
                    'user' => $user));
            $query = $qb->getQuery();
            $incoming_sessions = $query->getArrayResult();

            /* Get list of past sessions */
            $qb = $em->createQueryBuilder();
            $qb->select('s', 'r', 'sr')
                ->from('Entities\Session', 's')
                ->innerjoin('s.reservations', 'r', 'with', 'r.startDate < :date')
                ->leftjoin('s.requests', 'sr')
                ->orderBy('r.startDate', 'desc')
                ->where($qb->expr()->eq('s.training', ':training'));
            $qb->setParameters(array(
                    'training' => $training_id,
                    'date' => $today));
            $query = $qb->getQuery();
            $past_sessions = $query->getArrayResult();

            $body = $app->renderView('dashboard/training.html.twig', array(
                'user' => $user,
                'training' => $training,
                'incoming_sessions' => $incoming_sessions,
                'past_sessions' => $past_sessions,
            ));
            return new Response($body, 200, array('Cache-Control' => 's-maxage=3600, private'));
        })
        ->assert('training_id', '\d+')
        ->bind('training');

        $controllers->get('/new', function() use($app, $em)
        {
            $user = $app->user();

            /* Get list of users */
            $qb = $em->createQueryBuilder();
            $qb->select('u')
                ->from('Entities\User', 'u');
            $query = $qb->getQuery();
            $users = new Paginator($query, $fetchJoinCollection = true);

            /* Get list of available groups */
            $qb = $em->createQueryBuilder();
            $qb->select(array('g'))
                ->from('Entities\TrainingGroup', 'g');
            $query = $qb->getQuery();
            $groups = $query->getArrayResult();

            return $app->renderView('trainings/new.html.twig', array(
                'user' => $user,
                'users' => $users,
                'groups' => $groups
            ));
        })
        ->bind('new_training');

        $controllers->post('/new', function(Request $request) use($app, $em)
        {
            $user = $app->user();
            $data = $request->get('training', false);
            if($data)
            {
                $title = $data['title'];
                $description = $data['description'];
                $details = $data['details'];
                $group_id = $data['group_id'];
                $instructors = $request->get('instructors', array());

                $group = $em->find('Entities\TrainingGroup', $group_id);

                $training = new Training();
                $training->setTitle($title);
                $training->setDescription($description);
                $training->setDetails($details);
                $training->setGroup($group);
                $training->setOwner($user);
                foreach($instructors as $instructor_id)
                {
                    $instructor = $em->find('Entities\User', $instructor_id);
                    $training->addInstructor($instructor);
                }
                $em->persist($training);

                $em->flush();

                $app['monolog']->addInfo(sprintf("User '%s' created a new training program.",
                        $user->getUsername()));
                $admins = $em->getRepository('Entities\User')->getAdmins();
                if(count($admins) > 0)
                {
                    $draft = \Swift_Message::newInstance()
                        ->setSubject(sprintf("[%s] New training program",
                                $app['base_url']))
                        ->setFrom(array($app['config']['formation']['email_from']))
                        ->setContentType("text/html");
                    $params = array(
                        'base_url' => $app['base_url'],
                        'training' => $training,
                        'user' => $user,
                    );
                    foreach($admins as $admin)
                    {
                        $params['admin'] = $admin;
                        $message = clone $draft;
                        $message->setTo($admin->getEmail())
                            ->setBody($app->renderView('emails/training-new.html.twig', $params));
                        $app['mailer']->send($message);
                    }
                }
            }
            $url = $app->url('training', array(
                'training_id' => $training->getId()
            ));
            return $app->redirect($url);
        })
        ->bind('new_training_check');

        $controllers->get('/{training_id}/edit', function($training_id) use($app, $em)
        {
            $user = $app->user();

            /* get training */
            $training = $em->find('Entities\Training', $training_id);

            /* Get list of users */
            $qb = $em->createQueryBuilder();
            $qb->select('u')
                ->from('Entities\User', 'u');
            $query = $qb->getQuery();
            $users = new Paginator($query, $fetchJoinCollection = true);

            /* Get list of available groups */
            $qb = $em->createQueryBuilder();
            $qb->select(array('g'))
                ->from('Entities\TrainingGroup', 'g');
            $query = $qb->getQuery();
            $groups = $query->getArrayResult();

            return $app->renderView('trainings/edit.html.twig', array(
                'user' => $user,
                'users' => $users,
                'training' => $training,
                'groups' => $groups
            ));
        })
        ->assert('training_id', '\d+')
        ->bind('edit_training');

        $controllers->post('/{training_id}/edit', function($training_id, Request $request) use($app, $em)
        {
            $user = $app->user();
            $data = $request->get('training', false);
            if($data)
            {
                $title = $data['title'];
                $description = $data['description'];
                $details = $data['details'];
                $group_id = $data['group_id'];
                $instructors = $request->get('instructors', array());

                $group = $em->find('Entities\TrainingGroup', $group_id);

                $training = $em->find('Entities\Training', $training_id);
                $training->setTitle($title);
                $training->setDescription($description);
                $training->setDetails($details);
                $training->setGroup($group);
                $training->getInstructors()->clear();
                foreach($instructors as $instructor_id)
                {
                    $instructor = $em->find('Entities\User', $instructor_id);
                    $training->addInstructor($instructor);
                }

                $em->flush();
            }
            $url = $app->url('training', array(
                'training_id' => $training->getId()
            ));
            return $app->redirect($url);
        })
        ->bind('edit_training_check');

        $controllers->post('/set', function(Request $request) use($app, $em)
        {
            $user = $app->user();
            $action = $request->get('action', false);
            $training_id = $request->get('training_id', false);
            if($training_id && $action)
            {
                $training = $em->find('Entities\Training', $training_id);
                if('enable' === $action)
                {
                    $app['monolog']->addInfo(sprintf("Admin '%s' enabled a training program.",
                            $user->getUsername()));
                    $training->setIsEnabled(true);
                    $owner = $training->getOwner();
                    $em->flush();
                    $message = \Swift_Message::newInstance()
                        ->setSubject(sprintf("[%s] Your training program is now enabled",
                                $app['base_url']))
                        ->setFrom(array($app['config']['formation']['email_from']))
                        ->setTo($owner->getEmail())
                        ->setBody($app->renderView('emails/training-enable.html.twig', array(
                                'base_url' => $app['base_url'],
                                'training' => $training,
                                'user' => $owner)))
                        ->setContentType("text/html");
                    $app['mailer']->send($message);
                }
                elseif('disable' === $action)
                {
                    $app['monolog']->addInfo(sprintf("Admin '%s' disabled a training program.",
                            $user->getUsername()));
                    $training->setIsEnabled(false);
                    $owner = $training->getOwner();
                    $em->flush();
                    $message = \Swift_Message::newInstance()
                        ->setSubject(sprintf("[%s] Your training program is now disabled",
                                $app['base_url']))
                        ->setFrom(array($app['config']['formation']['email_from']))
                        ->setTo($owner->getEmail())
                        ->setBody($app->renderView('emails/training-disable.html.twig', array(
                                'base_url' => $app['base_url'],
                                'training' => $training,
                                'user' => $owner)))
                        ->setContentType("text/html");
                    $app['mailer']->send($message);
                }

                return $app->json(array('action' => $action));
            }
            else
            {
                return $app->json(null);
            }
        })
        ->bind('set_training_check');

        $controllers->post('/delete', function(Request $request) use($app, $em)
        {
            $user = $app->user();
            $training_id = $request->get('training_id', false);
            if($training_id)
            {
                $training = $em->find('Entities\Training', $training_id);
                $owner = $training->getOwner();
                $title = $training->getTitle();
                $em->remove($training);
                $em->flush();

                $app['monolog']->addInfo(sprintf("Admin '%s' deleted a training program.",
                        $user->getUsername()));

                $message = \Swift_Message::newInstance()
                    ->setSubject(sprintf("[%s] Your training program has been deleted",
                            $app['base_url']))
                    ->setFrom(array($app['config']['formation']['email_from']))
                    ->setTo($owner->getEmail())
                    ->setBody($app->renderView('emails/training-delete.html.twig', array(
                            'base_url' => $app['base_url'],
                            'title' => $title,
                            'user' => $owner)))
                    ->setContentType("text/html");
                $app['mailer']->send($message);
                $draft = \Swift_Message::newInstance()
                    ->setSubject(sprintf("[%s] New training program",
                            $app['base_url']))
                    ->setFrom(array($app['config']['formation']['email_from']))
                    ->setContentType("text/html");
                $params = array(
                    'base_url' => $app['base_url'],
                    'training' => $training,
                    'user' => $user,
                );
                foreach($admins as $admin)
                {
                    $params['admin'] = $admin;
                    $message = clone $draft;
                    $message->setTo($owner->getEmail())
                        ->setBody($app->renderView('emails/training-delete.html.twig', $params));
                    $app['mailer']->send($message);
                }
                return $app->json(array(null));
            }
            return $app->json(array(null));
        })
        ->bind('delete_training_check');


        return $controllers;
    }
}
