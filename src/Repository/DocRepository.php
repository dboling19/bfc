<?php

namespace App\Repository;

use App\Entity\Doc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Doc>
 *
 * @method Doc|null find($id, $lockMode = null, $lockVersion = null)
 * @method Doc|null findOneBy(array $criteria, array $orderBy = null)
 * @method Doc[]    findAll()
 * @method Doc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocRepository extends ServiceEntityRepository
{

  private $dir;

  public function __construct(ManagerRegistry $registry)
  {
      parent::__construct($registry, Doc::class);
  }

  
  /**
  * @return Doc[] Returns an array of Doc objects dependent on 
  * parent directory
  * 
  */
  public function findAllIn($cwd): array
  {
     return $this->createQueryBuilder('doc')
        ->leftJoin('doc.directory', 'dir')
        ->andWhere('NOT dir.name like :trash')
        ->andWhere('dir.parent like :cwd')
        ->setParameter('trash', '%trash%')
        ->setParameter('cwd', $cwd)
        ->getQuery()
        ->getResult()
      ;
  }


    /**
  * @return Doc[] Returns an array of Doc objects
  */
  public function findTrash(): array
  {
     return $this->createQueryBuilder('doc')
        ->leftJoin('doc.directory', 'dir')
        ->andWhere('dir.name like :trash')
        ->setParameter('trash', '%trash%')
        ->getQuery()
        ->getResult()
      ;
  }


  public function save(Doc $entity, bool $flush = false): void
  {
      $this->getEntityManager()->persist($entity);

      if ($flush) {
          $this->getEntityManager()->flush();
      }
  }

  public function remove(Doc $entity, bool $flush = false): void
  {
      $this->getEntityManager()->remove($entity);

      if ($flush) {
          $this->getEntityManager()->flush();
      }
  }

//    /**
//     * @return Doc[] Returns an array of Doc objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Doc
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
