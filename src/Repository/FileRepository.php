<?php

namespace App\Repository;

use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<File>
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

   public function findByOwner(User $owner): array
   {
       return $this->createQueryBuilder('f')
           ->andWhere('f.owner = :owner')
           ->setParameter('owner', $owner)
           ->getQuery()
           ->getResult();
   }

   public function findByOwnerAndCategory(User $owner, array $categoryIds): array
   {
       return $this->createQueryBuilder('f')
           ->innerJoin('f.category', 'c')
           ->andWhere('f.owner = :owner')
           ->andWhere('c.id IN (:category)')
           ->setParameter('owner', $owner)
           ->setParameter('category', $categoryIds)
           ->getQuery()
           ->getResult();
   }
}
