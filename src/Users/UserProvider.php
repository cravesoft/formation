<?php

namespace Users;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Entities\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\EntityManager;

class UserProvider implements UserProviderInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function loadUserByUsername($username)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select(array('u'))
        ->from('Entities\User', 'u')
        ->where($qb->expr()->eq('u.username', ':username'));
        $qb->setParameter('username', $username);
        $query = $qb->getQuery();
        $user = $query->getSingleResult();
        if(null === $user)
        {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username));
        }
        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if(!$user instanceof User)
        {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Entities\User';
    }
}
