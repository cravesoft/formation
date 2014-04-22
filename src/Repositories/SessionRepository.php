<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

class SessionRepository extends EntityRepository
{
    public function getSessions()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('s')
            ->from('Entities\Session', 's');
        return $qb->getQuery()->getResult();
    }

    public function getAdmins()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('s')
            ->from('Entities\Session', 's')
            ->where('s.roles = :role')
            ->setParameter('role', 'ROLE_ADMIN');
        return $qb->getQuery()->getResult();
    }

    public function getSize()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(s)')
            ->from('Entities\Session', 's');
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function countByUser($user)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('s.id')
            ->from('Entities\Session', 's')
            ->innerjoin('s.requests', 'sr')
            ->innerjoin('sr.user', 'u', 'with', 'u.id = :user');
        $params = array('user' => $user);
        return getNumResults($em, 'Entities\Session', $qb->getDQL(), $params);
    }
}
