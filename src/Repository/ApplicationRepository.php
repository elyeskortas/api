<?php

namespace App\Repository;

use App\Entity\Application;
use App\Service\UserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Application>
 *
 * @method Application|null find($id, $lockMode = null, $lockVersion = null)
 * @method Application|null findOneBy(array $criteria, array $orderBy = null)
 * @method Application[]    findAll()
 * @method Application[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationRepository extends ServiceEntityRepository
{
    private object $userService;
    private object $userRepository;
    private object $companyRepository;

    public function __construct(
        ManagerRegistry $registry,
        UserService $userService,
        UserRepository $userRepository,
        CompanyRepository $companyRepository
    )
    {
        $this->userService = $userService;
        $this->userRepository = $userRepository;
        $this->companyRepository = $companyRepository;
        parent::__construct($registry, Application::class);
    }

    
    /**
     * Set data from the request
     *
     * @param mixed $request
     * @param mixed $photo
     * @return Application
     */
    public function setData($request, $photo)
    {
        $application = new Application();
        $this->setApplicationData($application, $request, $photo);
        $application->setCreatedAt();
        $application->setIsActive(true);
        $application->setCreatedBy($this->userService->getUserData());
        $this->saveApplication($application);
        return $application;
    }

    /**
     * Set application data based on the request, photo, and optional password.
     *
     * @param Application $application
     * @param mixed $request
     * @param mixed $photo
     * @return void
     */
    private function setApplicationData(Application $application, $request, $photo)
    {
        $application->setName($request->get('name') ?? $application->getName());
        $application->setDescription($request->get('description') ?? $application->getDescription());
        $application->setPrimaryColor($request->get('primaryColor') ?? $application->getPrimaryColor());
        $application->setSecondaryColor($request->get('secondaryColor') ?? $application->getSecondaryColor());
        $application->setType($request->get('type') ?? $application->getType());
        $application->setLogo($request->get('useCompanyLogo') === 'true' ? $this->companyRepository->find($request->get('company'))->getPhoto() : $photo ?? $application->getLogo());
        $application->setCompany($this->companyRepository->find($request->get('company'))  ?? $application->getCompany());
        $application->setDomain($request->get('domain') ?? $application->getDomain());
        $application->setModal($request->get('modal') ?? $application->getModal());
        $application->setEmail($request->get('email') ?? $application->getEmail());
        $application->setPhone($request->get('website') ?? $application->getPhone());
        $application->setTemplate($request->get('template') ?? $application->getTemplate());
        $application->setWebsite($request->get('phoneNumber') ?? $application->getWebsite());
        $application->setAddress($request->get('address') ?? $application->getAddress());

    }

        /**
     * Update application data based on the request, photo, and application ID.
     *
     * @param mixed $request
     * @param mixed $photo
     * @param int $id
     * @return Application
     */
    public function updateData($request, $photo, $id)
    {
        $application = $this->find($id);
        $this->setApplicationData($application, $request, $photo);
        $application->setUpdatedAt();
        $application->setUpdatedBy($this->userService->getUserData());
        $this->saveApplication($application);
        return $application;
    }


    /**
     * Activate or deactivate a application based on their ID.
     *
     * @param int $id
     * @return Application
     */
    public function activeAndInactive($id)
    {
        $application = $this->find($id);
        $application->getIsActive() ? $application->setIsActive(false) :  $application->setIsActive(true);
        $application->setUpdatedAt();
        $this->saveApplication($application);
        return $application;
    }

    /**
     * Get an array representation of the application data.
     *
     * @param Application $application
     * @return array
     */
    public function getApplication($application)
    {
        return array(
            "id" =>  $application->getId(),
            "name" =>  $application->getName(),
            "description" =>  $application->getDescription(),
            "primaryColor" =>  $application->getPrimaryColor(),
            "secondaryColor" =>  $application->getSecondaryColor(),
            "type" =>  $application->getType(),
            "logo" =>  $application->getLogo(),
            "company" => $this->companyRepository->getCompany($application->getCompany()),
            "domain" =>  $application->getDomain(),
            "modal" =>  $application->getModal(),
            "email" =>  $application->getEmail(),
            "website" =>  $application->getWebsite(),
            "template" =>  $application->getTemplate(),
            "phoneNumber" =>  $application->getPhone(),
            "address" =>  $application->getAddress(),
            "isActive" => $application->getIsActive(),
            "createdAt" => $application->getCreatedAt()->format('d/m/Y H:i:s'),
            "createdBy" => $this->userRepository->getUserMinimize($application->getCreatedBy()),
            "updatedAt" => $application->getUpdatedAt() ? $application->getUpdatedAt()->format('d/m/Y H:i:s') : null,
            "updatedBy" => $application->getUpdatedBy() ? $this->userRepository->getUserMinimize($application->getUpdatedBy()) : null,
        );
    }

    /**
     * Get an array representation of all application data.
     *
     * @return array
     */
    public function getAllApplications()
    {
        $applications = $this->findAll();
        $data = [];
        foreach ($applications as $application) {
            $data[] = $this->getApplication($application);
        }
        return $data;
    }

    /**
     * Save application in database
     * 
     * @param Application $application
     * @return Application
     */
    private function saveApplication($application)
    {
        $this->getEntityManager()->persist($application);
        $this->getEntityManager()->flush();
        return $application;
    }


}
