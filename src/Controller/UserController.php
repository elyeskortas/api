<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use App\Controller\ApiController;

#[Route('/api/user', name: 'app_user')]
class UserController extends AbstractController
{
    private $emailService;
    private $userRepository;
    private $apiController;
    private $validator;
    private $logger;

    public function __construct(
        EmailService $emailService,
        UserRepository $userRepository,
        ApiController $apiController,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->emailService = $emailService;
        $this->userRepository = $userRepository;
        $this->apiController = $apiController;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    #[Route('/get', name: 'app_user_get', methods: ['GET'])]
    public function getAll()
    {
        $users = $this->userRepository->findAll();

        $responseData = [];
        foreach ($users as $user) {
            $responseData[] = [
                'ID' => $user->getId(),
                'Email' => $user->getEmail(),
                'First Name' => $user->getFirstName(),
                'Last Name' => $user->getLastName(),
                'Phone Number' => $user->getPhoneNumber(),
            ];
        }

        $response = [
            'totalData' => count($users),
            'data' => $responseData,
        ];

        return $this->apiController->respondWithSuccess($response);
    }

    #[Route('/get/{id}', name: 'app_user_get_one', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->apiController->respondNotFound('User not found');
        }
        return $this->apiController->respondWithSuccess(['user' => $user]);
    }

    #[Route('/add', name: 'app_user_add', methods: ['POST'])]
    public function addUser(Request $request, EntityManagerInterface $entityManager)
    {
        try {
            $request = $this->apiController->transformJsonBody($request);

            $file = $request->files->get('photo');
            $photo = null;
            if ($file) {
                $filename = $request->get('email') . '.' . $file->guessExtension();
                $photo = '/photos/user/' . $filename;
                $file->move('photos/user', $filename);
            }

            $user = $this->userRepository->setData($request, $photo);
            $entityManager->persist($user);
            $entityManager->flush();

            $subject = 'Your user account has been added';
            $body = $this->renderView('send-to-active-account.html.twig', ['token' => $user->getTokenUpdatePassword(), 'user' => $user]);
            $this->emailService->sendEmail($user->getEmail(), $subject, $body);

            return $this->apiController->respondCreated(['user' => $user], "User added successfully");
        } catch (\Exception $e) {
            $this->logger->error('Failed to add user: ' . $e->getMessage());
            return $this->apiController->respondWithErrors('An error occurred while adding the user.');
        }
    }

    #[Route('/update/{id}', name: 'app_user_update', methods: ['POST'])]
    public function updateUser(Request $request, EntityManagerInterface $entityManager, int $id)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->apiController->respondUnauthorized("Not authorized to use this function");
        }
    
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->apiController->respondNotFound('User not found');
        }

        $request = $this->apiController->transformJsonBody($request);

        $file = $request->files->get('photo');
        $photo = null;
        if ($file) {
            $filename = $request->get('email') . '.' . $file->guessExtension();
            $photo = '/photos/user/' . $filename;
            $file->move('photos/user', $filename);
        }

        $errors = $this->validator->validate($this->userRepository->validateData($request, 'update', $id));
        if (count($errors) > 0) {
            $errorString = implode(', ', $errors);
            return $this->apiController->respondValidationError($errorString);
        }

        $user = $this->userRepository->updateData($request, $photo, $id);
        $entityManager->flush();

        return $this->apiController->respondCreated(['user' => $user], "User updated successfully");
    }

    #[Route('/update_active/{id}', name: 'app_user_active_or_inactive', methods: ['POST'])]
    public function activeInactive(EntityManagerInterface $entityManager, int $id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->apiController->respondUnauthorized("Not authorized to use this function");
        }

        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->apiController->respondNotFound('User not found');
        }

        $user->setIsActive(!$user->getIsActive());
        $entityManager->flush();

        return $this->apiController->respondWithSuccess(['user' => $user]);
    }}