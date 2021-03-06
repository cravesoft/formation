<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function getUsers()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('u')
            ->from('Entities\User', 'u');
        return $qb->getQuery()->getResult();
    }

    public function getSize()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(u)')
            ->from('Entities\User', 'u');
        return $qb->getQuery()->getSingleScalarResult();
    }
}
