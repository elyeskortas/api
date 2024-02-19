<?php

namespace App\Controller;

use App\Repository\CompanyRepository;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/company/v1', name: 'app_company')]
class CompanyController extends AbstractController
{
    private $companyRepository;
    private $apiController;

    public function __construct(
        CompanyRepository $companyRepository,
        ApiController $apiController,
    ) {
        // Dependency injection for various services and repositories
        $this->companyRepository = $companyRepository;
        $this->apiController = $apiController;
    }

    #[Route('/get', name: 'app_company_get')]
    public function getAll()
    {
        // Get all company data and respond with success
        $data = $this->companyRepository->getAllCompanies();
        $response = [
            'totalData' => count($this->companyRepository->findAll()),
            'data' => $data
        ];
        return $this->apiController->respondWithSuccess($response);
    }

    #[Route('/get/{id}', name: 'app_user_get_one')]
    public function getOne($id)
    {
        // Get one user data and respond with success
        return $this->apiController->respondWithSuccess($this->companyRepository->getCompany($this->companyRepository->find($id)));
    }

    #[Route('/add', name: 'app_company_add')]
    public function addCompany(Request $request)
    {
        $time = new DateTimeImmutable('now', new DateTimeZone('Africa/Tunis'));

        // Transform the JSON body of the request
        $request = $this->apiController->transformJsonBody($request);

        // Handle company photo upload
        $file = $request->files->get('photo');
        $photo = null;
        if ($file) {
            $filename =  $request->get('name') . $time->format('dmYHis') . '.' . $file->guessExtension();
            $photo = '/photos/company/' . $filename;
            $file->move('photos/company', $filename);
        }

        // Save company data and send activation email
        $company = $this->companyRepository->setData($request, $photo);
      
        return $this->apiController->respondCreated($this->companyRepository->getCompany($company), "Company added successefully");
    }

    #[Route('/update/{id}', name: 'app_company_update')]
    public function updateCompany(Request $request, $id)
    {
        $time = new DateTimeImmutable('now', new DateTimeZone('Africa/Tunis'));

        // Check if company with the given ID exists
        if (!$this->companyRepository->find($id)) {
            return $this->apiController->respondNotFound('Company not found');
        }

        // Transform the JSON body of the request
        $request = $this->apiController->transformJsonBody($request);

        // Handle company photo upload
        $file = $request->files->get('photo');

        $photo = null;
        if ($file) {
            $filename =  $request->get('name') . $time->format('dmYHis') . '.' . $file->guessExtension();
            $photo = '/photos/company/' . $filename;
            $file->move('photos/company', $filename);
        }

        // Save updated company data
        $company = $this->companyRepository->updateData($request, $photo, $id);
        return $this->apiController->respondCreated($this->companyRepository->getCompany($company) , "Company updated successefully");
    }

    #[Route('/update_active/{id}', name: 'app_company_active_or_inactive')]
    public function activeInactive($id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->apiController->respondUnauthorized("Not authorized to use this function");
        }

        // Check if company with the given ID exists
        if (!$this->companyRepository->find($id)) {
            return $this->apiController->respondNotFound('Company not found');
        }

        // Activate or deactivate company and respond with success
        $company = $this->companyRepository->activeAndInactive($id);
        return $this->apiController->respondWithSuccess($this->companyRepository->getCompany($company));
    }
}
