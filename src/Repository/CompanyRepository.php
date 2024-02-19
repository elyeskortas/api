<?php

namespace App\Repository;

use App\Entity\Company;
use App\Service\UserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    private object $userService;
    private object $userRepository;

    public function __construct(
        ManagerRegistry $registry,
        UserService $userService,
        UserRepository $userRepository
    )
    {
        $this->userService = $userService;
        $this->userRepository = $userRepository;
        parent::__construct($registry, Company::class);
    }


    /**
     * Set data from the request, generate a random password, and persist the Company instance.
     *
     * @param mixed $request
     * @param mixed $photo
     * @return Company
     */
    public function setData($request, $photo)
    {
        $company = new Company();
        $this->setCompanyData($company, $request, $photo);
        $company->setCreatedAt();
        $company->setCreatedBy($this->userService->getUserData());
        $this->saveCompany($company);
        return $company;
    }

    /**
     * Update company data based on the request, photo, and company ID.
     *
     * @param mixed $request
     * @param mixed $photo
     * @param int $id
     * @return Company
     */
    public function updateData($request, $photo, $id)
    {
        $company = $this->find($id);
        $this->setCompanyData($company, $request, $photo);
        $company->setUpdatedAt();
        $company->setUpdatedBy($this->userService->getUserData());
        $this->saveCompany($company);
        return $company;
    }

    /**
     * Set company data based on the request, photo, and optional password.
     *
     * @param Company $company
     * @param mixed $request
     * @param mixed $photo
     * @return void
     */
    private function setCompanyData(Company $company, $request, $photo)
    {
        $company->setName($request->get('name') ?? $company->getName());
        $company->setDescription($request->get('description') ?? $company->getDescription());
        $company->setWebsite($request->get('website') ?? $company->getWebsite());
        $company->setEmail($request->get('email') ?? $company->getEmail());
        $company->setPhoneNumber($request->get('phoneNumber') ?? $company->getPhoneNumber());
        $company->setPhoto($photo ?? $company->getPhoto());
        $company->setIsActive(true);
    }

    /**
     * Activate or deactivate a company based on their ID.
     *
     * @param int $id
     * @return Company
     */
    public function activeAndInactive($id)
    {
        $company = $this->find($id);
        $company->getIsActive() ? $company->setIsActive(false) :  $company->setIsActive(true);
        $company->setUpdatedAt();
        $this->saveCompany($company);
        return $company;
    }

    /**
     * Get an array representation of the company data.
     *
     * @param Company $company
     * @return array
     */
    public function getCompany($company)
    {
        return array(
            "id" =>  $company->getId(),
            "photo" =>  $company->getPhoto(),
            "name" =>  $company->getName(),
            "description" =>  $company->getDescription(),
            "email" =>  $company->getEmail(),
            "phoneNumber" =>  $company->getPhoneNumber(),
            "website" =>  $company->getWebsite(),
            "isActive" => $company->getIsActive(),
            "createdAt" => $company->getCreatedAt()->format('d/m/Y H:i:s'),
            "createdBy" => $this->userRepository->getUserMinimize($company->getCreatedBy()),
            "updatedAt" => $company->getUpdatedAt() ? $company->getUpdatedAt()->format('d/m/Y H:i:s') : null,
            "updatedBy" => $company->getUpdatedBy() ? $this->userRepository->getUserMinimize($company->getUpdatedBy()) : null,
        );
    }

    /**
     * Get an array representation of all company data.
     *
     * @return array
     */
    public function getAllCompanies()
    {
        $companies = $this->findAll();
        $data = [];
        foreach ($companies as $company) {
            $data[] = $this->getCompany($company);
        }
        return $data;
    }

    /**
     * Save company in database
     * 
     * @param Company $company
     * @return Company
     */
    private function saveCompany($company)
    {
        $this->getEntityManager()->persist($company);
        $this->getEntityManager()->flush();
        return $company;
    }

}
