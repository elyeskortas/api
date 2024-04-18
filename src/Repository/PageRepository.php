<?php

namespace App\Repository;

use App\Entity\ItemPage;
use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 *
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    private object $applicationRepository;
    private object $itemPageRepository;
    public function __construct(
        ManagerRegistry $registry,
        ApplicationRepository $applicationRepository,
        ItemPageRepository $itemPageRepository
    ) {
        $this->itemPageRepository = $itemPageRepository;
        $this->applicationRepository = $applicationRepository;
        parent::__construct($registry, Page::class);
    }


    /**
     * Set data from the request,
     *
     * @param mixed $request
     * @param mixed $photo
     * @return Page
     */
    public function setData($request)
    {
        $this->setPageData($request);
        // return $page;
    }

    /**
     * Set page data based on the request, photo, and optional password.
     *
     * @param Page $page
     * @param mixed $request
     * @param mixed $photo
     * @return void
     */
    private function setPageData($request)
    {
        $generatePage = $request->get('generatePage');
        $pages = $request->get('pages');

        foreach ($pages as $data) {
            $page = new Page();
            $page->setTitle($data['title']);
            $page->setType($data['type']);
            $page->setApplication($this->applicationRepository->find($request->get('applicationId')));
            $this->savePage($page);
            foreach ($generatePage as $gPage) {
                if (array_key_exists('title', $gPage)) {
                    if ($data['title'] === $gPage['title']) {
                        foreach ($gPage['data'] as $data) {
                            if ($data) {
                                $itemPage = new ItemPage();
                                $itemPage->setPage($page);
                                $itemPage->setData(json_encode($data));
                                $this->getEntityManager()->persist($itemPage);
                                $this->getEntityManager()->flush();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Application $application
     * @return array
     */
    public function getPageByApplication($application)
    {
        $pages = $this->findBy(['application' => $application]);
        $result = array();

        foreach ($pages as $page) {
            $items = $this->itemPageRepository->findBy(['page' => $page]);
            foreach ($items as $item) {
                $data = array(
                    json_decode($item->getData()),
                );
            }

            $result[$page->getTitle()] = array(
                "id" => $page->getId(),
                "title" => $page->getTitle(),
                "type" => $page->getType(),
                "data" => is_array($data) ? $data : [],
            );
        }
   

        return $result;
    }


    /**
     * Save page in database
     * 
     * @param Page $page
     * @return Page
     */
    private function savePage($page)
    {
        $this->getEntityManager()->persist($page);
        $this->getEntityManager()->flush();
        return $page;
    }
}
