<?php

namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Entities\Reservation;

class ReservationControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        $em = $app['orm.em'];

        $controllers->get('/', function(Request $request) use($app, $em)
        { 
            $room_id = $request->get('room_id', false);
            $start_date = date('Y-m-d H:i:s', strtotime($request->get('start_date')));
            $end_date = date('Y-m-d H:i:s', strtotime($request->get('end_date')));
            $qb = $em->createQueryBuilder();
            $qb->select(array('r', 's', 't', 'ro'))
                ->from('Entities\Reservation', 'r')
                ->leftJoin('r.session', 's')
                ->leftJoin('s.training', 't')
                ->where($qb->expr()->andX(
                    $qb->expr()->gt('r.startDate', ':start'),
                    $qb->expr()->lt('r.endDate', ':end')
                ));
            $params = array('start' => $start_date, 'end' => $end_date);
            if($room_id)
            {
                $qb->innerJoin('r.room', 'ro', 'with', 'ro.id = :room');
                $params['room'] = $room_id;
            }
            else
            {
                $qb->leftJoin('r.room', 'ro');
            }
            $qb->setParameters($params);
            $query = $qb->getQuery();
            $reservations = $query->getArrayResult();
            $body = json_encode(array(
                'reservations' => $reservations,
            ));
            $response = new Response($body, 200, array('Cache-Control' => 's-maxage=3600, private'));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        })
        ->bind('reservations');

        $controllers->post('/new', function(Request $request) use($app, $em)
        {
            $user = $app->user();
            $room_id = $request->get('room_id', false);
            if($room_id === false)
            {
                return $app->json(null);
            }
            if(($timestamp = strtotime($request->get('start_date'))) === false)
            {
                return $app->json(null);
            }
            $start_date = new \DateTime($request->get('start_date'));
            if(($timestamp = strtotime($request->get('end_date'))) === false)
            {
                return $app->json(null);
            }
            $end_date = new \DateTime($request->get('end_date'));

            $room = $em->find('Entities\Room', $room_id);

            $reservation = new Reservation();
            $reservation->setRoom($room);
            $reservation->setStartDate($start_date);
            $reservation->setEndDate($end_date);
            $em->persist($reservation);
            $em->flush();

            $app['monolog']->addInfo(sprintf("User '%s' created a new reservation.",
                    $user->getUsername()));

            return $app->json(array(
                'reservation_id' => $reservation->getId(),
            ));
        })
        ->bind('new_reservation_check');

        return $controllers;
    }
}
