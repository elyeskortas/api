<?php

namespace App\Controller;

use App\Repository\ApplicationRepository;
use App\Repository\ItemPageRepository;
use App\Repository\MenuRepository;
use App\Repository\PageRepository;
use App\Service\ApplicationService;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/application/v1', name: 'app_application')]
class ApplicationController extends AbstractController
{
    private $applicationRepository;
    private $pageRepository;
    private $itemPageRepository;
    private $menuRepository;
    private $apiController;
    private $applicationService;

    public function __construct(
        ApplicationRepository $applicationRepository,
        PageRepository $pageRepository,
        ItemPageRepository $itemPageRepository,
        MenuRepository $menuRepository,
        ApplicationService $applicationService,
        ApiController $apiController,
    ) {
        // Dependency injection for various services and repositories
        $this->applicationRepository = $applicationRepository;
        $this->pageRepository = $pageRepository;
        $this->itemPageRepository = $itemPageRepository;
        $this->menuRepository = $menuRepository;
        $this->applicationService = $applicationService;
        $this->apiController = $apiController;
    }

    #[Route('/get', name: 'app_application_get')]
    public function getAll()
    {
        // Get all application data and respond with success
        $data = $this->applicationRepository->getAllApplications();
        $response = [
            'totalData' => count($this->applicationRepository->findAll()),
            'data' => $data
        ];
        return $this->apiController->respondWithSuccess($response);
    }

    #[Route('/get/{id}', name: 'app_user_get_one')]
    public function getOne($id)
    {
        // Get one user data and respond with success
        return $this->apiController->respondWithSuccess($this->applicationRepository->getApplication($this->applicationRepository->find($id)));
    }

    #[Route('/add', name: 'app_application_add')]
    public function addApplication(Request $request)
    {
        $time = new DateTimeImmutable('now', new DateTimeZone('Africa/Tunis'));

        // Transform the JSON body of the request
        $request = $this->apiController->transformJsonBody($request);

        $photo = null;

        if ($request->get('useCompanyLogo') === 'false') {
            // Handle application photo upload
            $file = $request->files->get('logo');
            if ($file) {
                $filename =  $request->get('name') . $time->format('dmYHis') . '.' . $file->guessExtension();
                $photo = '/photos/application/' . $filename;
                $file->move('photos/application', $filename);
            }
        }
        // Save application data and send activation email
        $application = $this->applicationRepository->setData($request, $photo);

        return $this->apiController->respondCreated($this->applicationRepository->getApplication($application), "Application added successefully");
    }

    #[Route('/generate_page', name: 'app_application_add_pages')]
    public function generatePage(Request $request)
    {
        // Transform the JSON body of the request
        $request = $this->apiController->transformJsonBody($request);

        $this->menuRepository->setData($request);
        $this->pageRepository->setData($request);

        $this->applicationService->createApplication($this->applicationRepository->find($request->get('applicationId')));
        return $this->apiController->respondCreated($this->applicationRepository->getApplication($this->applicationRepository->find($request->get('applicationId'))), "Application added successefully");
    }

    #[Route('/update/{id}', name: 'app_application_update')]
    public function updateApplication(Request $request, $id)
    {
        $time = new DateTimeImmutable('now', new DateTimeZone('Africa/Tunis'));

        // Check if application with the given ID exists
        if (!$this->applicationRepository->find($id)) {
            return $this->apiController->respondNotFound('Application not found');
        }

        // Transform the JSON body of the request
        $request = $this->apiController->transformJsonBody($request);

        // Handle application photo upload
        $file = $request->files->get('logo');

        $photo = null;
        if ($file) {
            $filename =  $request->get('name') . $time->format('dmYHis') . '.' . $file->guessExtension();
            $photo = '/photos/application/' . $filename;
            $file->move('photos/application', $filename);
        }

        // Save updated application data
        $application = $this->applicationRepository->updateData($request, $photo, $id);
        return $this->apiController->respondCreated($this->applicationRepository->getApplication($application), "Application updated successefully");
    }

    #[Route('/update_active/{id}', name: 'app_application_active_or_inactive')]
    public function activeInactive($id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->apiController->respondUnauthorized("Not authorized to use this function");
        }
        // Check if application with the given ID exists
        if (!$this->applicationRepository->find($id)) {
            return $this->apiController->respondNotFound('Application not found');
        }

        // Activate or deactivate application and respond with success
        $application = $this->applicationRepository->activeAndInactive($id);
        return $this->apiController->respondWithSuccess($this->applicationRepository->getApplication($application));
    }
}
