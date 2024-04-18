<?php

namespace App\Repository;

use App\Entity\ItemPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ItemPage>
 *
 * @method ItemPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemPage[]    findAll()
 * @method ItemPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemPage::class);
    }


    /**
     * Set data from the request,
     *
     * @param mixed $request
     * @return ItemPage
     */
    public function setData($request)
    {
        $this->setItemPageData($request);
        // return $page;
    }

    /**
     * Set page data based on the request, photo, and optional password.
     *
     * @param ItemPage $itemPage
     * @param mixed $request
     * @param mixed $photo
     * @return void
     */
    private function setItemPageData($request)
    {
      
    }

  
    /**
     * Save page in database
     * 
     * @param ItemPage $itemPage
     * @return ItemPage
     */
    private function saveItemPage($itemPage)
    {
        $this->getEntityManager()->persist($itemPage);
        $this->getEntityManager()->flush();
        return $itemPage;
    }
}
