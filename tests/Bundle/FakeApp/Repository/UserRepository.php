<?php

declare(strict_types=1);

namespace WebPush\Tests\Bundle\FakeApp\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WebPush\Tests\Bundle\FakeApp\Entity\User;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @internal
 */
final class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $object): void
    {
        $this->getEntityManager()
            ->persist($object)
        ;
        $this->getEntityManager()
            ->flush()
        ;
    }
}
