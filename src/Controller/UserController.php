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
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Form\UserType;




#[Route('/api/user', name: 'app_user')]
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

    #[Route('/api/get', name: 'app_user_get')]
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

    #[Route('/api/get/{id}', name: 'app_user_get_one', methods: ['GET'])]
public function getOne(int $id): JsonResponse
{
    $user = $this->userRepository->find($id);
    if (!$user) {
        return $this->json(['error' => 'User not found'], 404);
    }
    return $this->json(['user' => $user]);
}
#[Route('/api/add', name: 'app_user_add')]
public function addUser(Request $request)
{
    // Transformer le corps JSON de la requête
    $request = $this->apiController->transformJsonBody($request);

    // Gérer le téléchargement de la photo de l'utilisateur
    $file = $request->files->get('photo');
    $photo = null;
    if ($file) {
        $filename =  $request->get('email') . '.' . $file->guessExtension();
        $photo = '/photos/user/' . $filename;
        $file->move('photos/user', $filename);
    }

    // Valider les données de l'utilisateur
    //$errors = $this->validator->validate($this->userRepository->validateData($request, 'add'));
    //$errorsArray = [];
    //if (count($errors) > 0) {
      //  foreach ($errors as $error) {
            // Ajoutez chaque message d'erreur au tableau d'erreurs
        //    $errorsArray[] = $error->getMessage();
        //}
        // Retournez les erreurs sous forme de tableau
        //return $this->apiController->respondValidationError($errorsArray);
    //}

    // Sauvegarder les données de l'utilisateur et envoyer l'e-mail d'activation
    $user = $this->userRepository->setData($request, $photo);
    $subject = 'Your user account has been added';
    $body = $this->renderView('send-to-active-account.html.twig', ['token' => $user->getTokenUpdatePassword(), 'user' => $user]);
    $this->emailService->sendEmail($user->getEmail(), $subject, $body);

    return $this->apiController->respondCreated($this->userRepository->getUser($user), "User added successfully");
}



#[Route('/api/update/{id}', name: 'app_user_update')]
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
    $errors = $this->validator->validate($this->userRepository->validateData($request, 'update', $id));
    if (count($errors) > 0) {
        // Convert the array $errors to a string
        $errorMessages = [];
        foreach ($errors as $error) {
            if (is_array($error) || is_object($error)) {
                // Handle nested arrays or objects
                $errorMessages[] = json_encode($error);
            } else {
                $errorMessages[] = $error;
            }
        }
        $errorString = implode(', ', $errorMessages);
        return $this->apiController->respondValidationError($errorString);
    }

    // Save updated user data
    $user = $this->userRepository->updateData($request, $photo, $id);
    return $this->apiController->respondCreated($this->userRepository->getUser($user), "User updated successfully");
}



#[Route('/api/update_active/{id}', name: 'app_user_active_or_inactive')]
public function activeInactive($id)
{
    if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
        return $this->apiController->respondUnauthorized("Not authorized to use this function");
    }

    // Check if user with the given ID exists
    $user = $this->userRepository->find($id);
    if (!$user) {
        return $this->apiController->respondNotFound('User not found');
    }

    // Activate or deactivate user
    $user->setIsActive(!$user->getIsActive());
    $this->entityManager->flush();

    return $this->apiController->respondWithSuccess($this->userRepository->getUser($user)); 
}}