<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 *
 * @method Menu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Menu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Menu[]    findAll()
 * @method Menu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuRepository extends ServiceEntityRepository
{
    private object $pageRepository;
    private object $applicationRepository;

    public function __construct(
        ManagerRegistry $registry,
        ApplicationRepository $applicationRepository,
        PageRepository $pageRepository,
    ) {
        $this->applicationRepository = $applicationRepository;
        $this->pageRepository = $pageRepository;
        parent::__construct($registry, Menu::class);
    }


    /**
     * Set data from the request,
     *
     * @param mixed $request
     * @param mixed $photo
     * @return Menu
     */
    public function setData($request)
    {
        $this->setMenuData($request);
        // return $menu;
    }

    /**
     * Set menu data based on the request, photo, and optional password.
     *
     * @param Menu $menu
     * @param mixed $request
     * @param mixed $photo
     * @return void
     */
    private function setMenuData($request)
    {
        foreach ($request->get('menu') as $data) {
            $menu = new Menu();
            $menu->setTitle($data['title']);
            $menu->setIcon($data['icon']);
            $page = $this->pageRepository->findOneBy([
                'title' => $data['title'],
                'application' => $this->applicationRepository->find($request->get('applicationId'))
            ]);
            $menu->setApplication($this->applicationRepository->find($request->get('applicationId')));
            $menu->setPage($page);
            $this->saveMenu($menu);
        }
    }


    /**
     * @param Application $application
     * @return array
     */
    public function getMenuByApplication($application)
    {
        $menus = $this->findBy(['application' => $application]);
        $result = array();
    
        foreach ($menus as $menu) {
            $result[$menu->getTitle()] = array(
                "id" =>  $menu->getId(),
                "title" =>  $menu->getTitle(),
                "icon" =>  $menu->getIcon(),
                "page" =>  $menu->getPage(),
            );
        }
    
        return $result;
    }


    /**
     * Save menu in database
     * 
     * @param Menu $menu
     * @return Menu
     */
    private function saveMenu($menu)
    {
        $this->getEntityManager()->persist($menu);
        $this->getEntityManager()->flush();
        return $menu;
    }
}
