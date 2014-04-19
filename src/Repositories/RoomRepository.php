<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

class RoomRepository extends EntityRepository
{
    public function getRooms()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r')
            ->from('Entities\Room', 'r');
        return $qb->getQuery()->getResult();
    }

    public function getSize()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(r)')
            ->from('Entities\Room', 'r');
        return $qb->getQuery()->getSingleScalarResult();
    }
}
