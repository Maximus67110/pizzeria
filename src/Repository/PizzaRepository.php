<?php

namespace App\Repository;

use App\Entity\Pizza;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pizza>
 *
 * @method Pizza|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pizza|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pizza[]    findAll()
 * @method Pizza[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PizzaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pizza::class);
    }

    public function search(int $minPrice = null, int $maxPrice = null, string $order = null, string $orderBy = null): array
    {
        $qb = $this->createQueryBuilder('p');

        if (isset($minPrice)) {
            $qb
                ->andWhere(':minPrice < p.price')
                ->setParameter('minPrice', $minPrice * 100);
        }

        if (isset($maxPrice)) {
            $qb
                ->andWhere(':maxPrice > p.price')
                ->setParameter('maxPrice', $maxPrice * 100);
        }

        if (isset($order, $orderBy)) {
            $qb
                ->orderBy('p.'.$order, $orderBy);
        }

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return Pizza[] Returns an array of Pizza objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Pizza
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
