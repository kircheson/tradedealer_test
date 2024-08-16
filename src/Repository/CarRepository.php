<?php

namespace App\Repository;

use App\Entity\Car;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Car>
 */
class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    /**
     * @return Car[] Returns an array of Car objects
     */
    public function getListCar($brand, $page, $limit): array
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.brand', 'b')
            ->andWhere('b.name = :brand')
            ->setParameter('brand', $brand);

        $paginator = new Paginator($queryBuilder);
        return $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
    }
}
