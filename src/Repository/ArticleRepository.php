<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Author;
use App\Entity\Category;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Article $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Article $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getAuthorList(int $limit = 5): array{
        $qb = $this->createQueryBuilder('p')
            ->select('a.id, 
                a.firstName, a.lastName, 
                count(p.id) as articleCount')
            ->join('p.author', 'a')
            ->groupBy('p.author')
            ->orderBy('articleCount','DESC')
            ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }

    public function getCategoryList(): array {
        $qb = $this->createQueryBuilder('p')
            ->select('c.id, 
                c.categoryName, 
                count(p.id) as articleCount')
            ->join('p.category', 'c')
            ->groupBy('p.category')
            ->orderBy('articleCount','DESC');
        return $qb->getQuery()->getResult();
    }

    public function getArticleBySearchTerm(string $searchTerm): Query{
        $qb = $this->createQueryBuilder('p')
            ->where('p.title LIKE :search 
                    OR p.content LIKE :search 
                    OR a.lastName LIKE :search 
                    OR a.firstName LIKE :search
                    OR c.categoryName LIKE :search'
            )
            ->join('p.author', 'a')
            ->join('p.category', 'c')
            ->orderBy('p.createdAt', 'DESC')
            ->setParameter(':search', "%$searchTerm%");

        return $qb->getQuery();
    }

    public function getArticlesGroupedByYears(): array{
        $qb = $this->createQueryBuilder('p')
            ->select('year(p.createdAt) as year, 
                      count(p.id) as articleCount')
            ->groupBy('year')
            ->orderBy('year', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getArticlesByYear(int $year): Query{
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('year(p.createdAt) = :year')
            ->join('p.author','a')
            ->setParameter(':year', $year)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery();
    }

    public function getArticlesByDate(DateTime $start, DateTime $end): Query{
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.createdAt BETWEEN :start AND :end')
            ->join('p.author','a')
            ->orderBy('p.createdAt', 'DESC')
            ->setParameter(':start', $start)
            ->setParameter(':end', $end)
            ->getQuery();
    }
    public function  getAllArticlesQuery(){

        return $this->createQueryBuilder('p')
            ->select('p')
            ->join('p.author','a')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery();
    }

    public function getArticlesByAuthors(Author $author){
        return $this->createQueryBuilder('p')
            ->select('p')
            ->join('p.author','a')
            ->where('a.id =:authorId')
            ->setParameter('authorId',$author->getId())
            ->orderBy('p.createdAt','DESC')
            ->getQuery()
            ;

    }

    public function getArticlesByCategory(Category $category){
        return $this->createQueryBuilder('p')
            ->select('p')
            ->join('p.category','c')
            ->join('p.author','a')
            ->where('c.id =:categoryId')
            ->setParameter('categoryId',$category->getId())
            ->orderBy('p.createdAt','DESC')
            ->getQuery()
            ;
    }
}
