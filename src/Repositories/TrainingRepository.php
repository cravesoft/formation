<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

class TrainingRepository extends EntityRepository
{
    public function getTrainings()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
            ->from('Entities\Training', 't');
        return $qb->getQuery()->getResult();
    }
}
