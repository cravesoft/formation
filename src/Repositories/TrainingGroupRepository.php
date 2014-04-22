<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

class TrainingGroupRepository extends EntityRepository
{
    public function getTrainingGroups()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
            ->from('Entities\TrainingGroup', 'g');
        return $qb->getQuery()->getResult();
    }

    public function getSize()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(g)')
            ->from('Entities\TrainingGroup', 'g');
        return $qb->getQuery()->getSingleScalarResult();
    }
}
