<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/user/v1', name: 'app_user')]
class UserController extends AbstractController
{
    private $emailService;
    private $userRepository;
    private $apiController;
    private $validator;

    public function __construct(
        EmailService $emailService,
        UserRepository $userRepository,
        ApiController $apiController,
        ValidatorInterface $validator
    ) {
        // Dependency injection for various services and repositories
        $this->emailService = $emailService;
        $this->userRepository = $userRepository;
        $this->apiController = $apiController;
        $this->validator = $validator;
    }

    #[Route('/get', name: 'app_user_get')]
    public function getAll()
    {
        // Get all user data and respond with success
        $data = $this->userRepository->getAllUsers();
        $response = [
            'totalData' => count($this->userRepository->findAll()),
            'data' => $data
        ];
        return $this->apiController->respondWithSuccess($response);
    }

    #[Route('/add', name: 'app_user_add')]
    public function addUser(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->apiController->respondUnauthorized("Not authorized to use this function");
        }

        // Transform the JSON body of the request
        $request = $this->apiController->transformJsonBody($request);

        // Handle user photo upload
        $file = $request->files->get('photo');
        $photo = null;
        if ($file) {
            $filename =  $request->get('email') . '.' . $file->guessExtension();
            $photo = '/photos/user/' . $filename;
            $file->move('photos/user', $filename);
        }

        // Validate user data
        $errors = $this->validator->validate($this->userRepository->validateData($request));
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->apiController->respondValidationError(($errorsArray));
        }

        // Save user data and send activation email
        $user = $this->userRepository->setData($request, $photo);
        $subject = 'Platform EKLECTIC : Your user account has been added';
        $body = $this->renderView('emails/send-to-active-account.html.twig', ['token' => $user->getTokenUpdatePassword(), 'user' => $user]);
        $this->emailService->sendEmail($user->getEmail(), $subject, $body);

        return $this->apiController->respondCreated($this->userRepository->getUser($user), "User added successefully");
    }

    #[Route('/update/{id}', name: 'app_user_update')]
    public function updateUser(Request $request, $id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->apiController->respondUnauthorized("Not authorized to use this function");
        }
        // Check if user with the given ID exists
        if (!$this->userRepository->find($id)) {
            return $this->apiController->respondNotFound('User not found');
        }

        // Transform the JSON body of the request
        $request = $this->apiController->transformJsonBody($request);

        // Handle user photo upload
        $file = $request->files->get('photo');
        $photo = null;
        if ($file) {
            $filename =  $request->get('email') . '.' . $file->guessExtension();
            $photo = '/photos/user/' . $filename;
            $file->move('photos/user', $filename);
        }

        // Validate user data
        $errors = $this->validator->validate($this->userRepository->validateData($request));
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->apiController->respondValidationError(($errorsArray));
        }

        // Save updated user data
        $user = $this->userRepository->updateData($request, $photo, $id);
        return $this->apiController->respondCreated($this->userRepository->getUser($user) , "User updated successefully");
    }

    #[Route('/update_active/{id}', name: 'app_user_active_or_inactive')]
    public function activeInactive($id)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->apiController->respondUnauthorized("Not authorized to use this function");
        }

        // Check if user with the given ID exists
        if (!$this->userRepository->find($id)) {
            return $this->apiController->respondNotFound('User not found');
        }

        // Activate or deactivate user and respond with success
        $user = $this->userRepository->activeAndInactive($id);
        return $this->apiController->respondWithSuccess($this->userRepository->getUser($user));
    }

    #[Route('/send_email_password/{id}', name: 'app_user_send_email_password')]
    public function forgotPasswordEmail($id, EntityManagerInterface $entityManager)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->apiController->respondUnauthorized("Not authorized to use this function");
        }

        // Find the user by ID
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->apiController->respondUnauthorized('User not found');
        }

        // Check if the user is inactive
        if (!$user->getIsActive()) {
            return $this->apiController->respondUnauthorized('This user is inactive');
        }

        // Send forgot password email and respond with success
        $user = $this->userRepository->forgotPasswordEmail($id);
        $subject = 'Platform EKLECTIC: forgot password';
        $body = $this->renderView('emails/send-to-update-password.html.twig', [
            'token' => $user->getTokenUpdatePassword(), 'user' => $user
        ]);
        $this->emailService->sendEmail($user->getEmail(), $subject, $body);

        return $this->apiController->respondWithSuccess('Email sent to user for changing password');
    }
}
