<?php

namespace App\Repository;

use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Student>
 */

class StudentRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Student::class);
    }

    public function searchStudents(?string $searchTerm = null, ?string $classroom = null): array {
        $qb = $this->createQueryBuilder('s');

        if ($searchTerm){
            $qb->andWhere('s.firstName LIKE :search OR s.lastName LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
        }

        if ($classroom) {
            $qb->andWhere('s.classroom = :classroom')
                ->setParameter('s.classroom', $classroom);
        }

        $qb->orderBy('s.lastName', 'ASC')
            ->addOrderBy('s.firstName', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findDistinctClassrooms(): array {
        return $this->createQueryBuilder('s')
            ->select('DISTINCT s.classroom')
            ->orderBy('s.classroom', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
}