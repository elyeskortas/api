<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/profile/v1', name: 'app_profile')]
class ProfileController extends AbstractController
{

    private $userRepository;
    private $apiController;
    private $validator;

    public function __construct(
        UserRepository $userRepository,
        ApiController $apiController,
        ValidatorInterface $validator
    ) {
        // Dependency injection for various services and repositories
        $this->userRepository = $userRepository;
        $this->apiController = $apiController;
        $this->validator = $validator;
    }

    #[Route('/update', name: 'app_profile_update')]
    public function updateUser(Request $request)
    {
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
        $user = $this->userRepository->updateData($request, $photo, $this->getUser()->getId());

        return $this->apiController->respondCreated($this->userRepository->getUser($user), "User profile updated successefully");
    }

    #[Route('/update_password', name: 'app_profile_update_password')]
    public function UpdatePassword(Request $request)
    {
        $errorsArray = [];
        $request = $this->apiController->transformJsonBody($request);

        $user = $this->getUser();
        if (!password_verify($request->get('oldPassword'), $user->getPassword())) {
            $errorsArray['old password'] = 'the old password is not correct';
        }
        if (!$request->get('password')) {
            $errorsArray['password'] = 'This value should not be null.';
        }
        if (!$request->get('confirmedPassword')) {
            $errorsArray['confirmedPassword'] = 'This value should not be null.';
        }
        if ($request->get('password') !== $request->get('confirmedPassword') && $request->get('password') && $request->get('confirmedPassword')) {
            $errorsArray['password'] = 'The passwords do not match.';
        }
        if (strlen($request->get('password')) < 8 && $request->get('password')) {
            $errorsArray['password'] = 'This value is too short. It should have 8 characters or more.';
        }
        if (strlen($request->get('confirmedPassword')) < 8 && $request->get('confirmedPassword')) {
            $errorsArray['confirmedPassword'] = 'This value is too short. It should have 8 characters or more.';
        }
        if (count($errorsArray) > 0) {
            return $this->apiController->respondValidationError(($errorsArray));
        }

        $user = $this->userRepository->UpdatePassword($request, $this->getUser()->getId());



        return $this->apiController->response($this->userRepository->getUser($user), 'password updated successfully');
    }


}
