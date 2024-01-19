<?php

namespace App\Controller;

use App\Controller\ApiController;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth/v1', name: 'app_auth')]
class AuthController extends AbstractController
{
    private $emailService;
    private $passwordHasher;
    private $userRepository;
    private $apiController;
    private $entityManager;

    public function __construct(
        EmailService $emailService,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        ApiController $apiController,
        EntityManagerInterface $entityManager
    ) {
        // Dependency injection for various services and repositories
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->apiController = $apiController;
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
    }

    #[Route('/login', name: 'app_auth_login')]
    public function login(Request $request, JWTTokenManagerInterface $JWTManager)
    {
        // Handle user login

        // Transform the JSON body of the request
        $request = $this->apiController->transformJsonBody($request);

        // Find the user by email
        $user = $this->userRepository->findOneBy(['email' => $request->get('email')]);

        // Check if the user exists
        if (!$user) {
            return $this->apiController->respondUnauthorized('User not found');
        }

        // Check if the entered password is valid
        if (!$user || !$this->passwordHasher->isPasswordValid($user,  $request->get('password'))) {
            // Update failed login information and respond with an error
            $user->setLastFailedLogin(new \DateTime());
            $user->setFailedLoginCount($user->getFailedLoginCount() + 1);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->apiController->respondUnauthorized('Invalid credentials');
        }

        // Generate JWT token and update user login information
        $token = $JWTManager->create($user);
        $user->setLastLogin(new \DateTime());
        $user->setLoginCount($user->getLoginCount() + 1);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Respond with success and user information
        $array = $this->userRepository->getUser($user);
        $array += [
            'token' => $token
        ];
        return $this->apiController->respondWithSuccess($array);
    }

    #[Route('/forgot_password', name: 'app_auth_forgot_password')]
    public function forgotPasswordEmail(EntityManagerInterface $entityManager, Request $request)
    {
        // Handle forgot password functionality

        // Transform the JSON body of the request
        $request = $this->apiController->transformJsonBody($request);

        // Retrieve email from the request
        $email = $request->get('email');

        // Validate email presence
        if (!$email) {
            return $this->apiController->respondValidationError("This value should not be null.");
        }

        // Convert email to lowercase and find user by email
        $email = strtolower($email);
        $user = $this->userRepository->findOneBy(['email' => $email]);

        // Check if the user exists
        if (!$user) {
            return $this->apiController->respondUnauthorized('User not found');
        }

        // Check if the user is active
        if (!$user->getIsActive()) {
            return $this->apiController->respondUnauthorized('This user is inactive');
        }

        // Generate a token, update user information, and send an email
        $token = $this->apiController->generateToken();
        $user->setTokenUpdatePassword($token);
        $user->setPasswordRequestedAt(new \DateTime());
        $entityManager->persist($user);
        $entityManager->flush();

        // Send email to the user for password change
        $subject = 'Platform EKLECTIC: forgot password';
        $body = $this->renderView('emails/send-to-update-password.html.twig', [
            'token' => $token, 'user' => $user
        ]);
        $this->emailService->sendEmail($email, $subject, $body);

        return $this->apiController->respondWithSuccess('Email sent to user for changing password');
    }

    #[Route('/reset_password', name: 'app_auth_reset_password')]
    public function resetPassword(Request $request)
    {
        // Handle password reset functionality

        $user = null;
        $toast = null;

        // Check if a token is present in the request
        if (isset($_GET['token'])) {
            $token = $_GET['token'];
            $user = $this->userRepository->findOneBy(['tokenUpdatePassword' => $token]);
        }

        // Create a form for resetting the password
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        // If user is found
        if ($user) {
            // Check if the form is submitted and valid
            if ($form->isSubmitted() && $form->isValid()) {
                $password = $form->getData()['password'];
                $confirmPassword = $form->getData()['resetPassword'];

                // Check if passwords match
                if ($password !== $confirmPassword) {
                    $toast = 'The passwords do not match.';
                } else {
                    // Update user password and send confirmation email
                    $user->setTokenUpdatePassword(null);
                    $user->setPassword($this->passwordHasher->hashPassword($user, $password));
                    $user->setIsActive(true);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    // Send confirmation email
                    $subject = 'Platform EKLECTIC : your password has been changed';
                    $body =  $this->renderView('emails/update-password.html.twig', [
                        'user' => $user,
                    ]);
                    $this->emailService->sendEmail($user->getEmail(), $subject, $body);
                }
            }
        }

        // Render the reset password form
        return $this->render(
            'emails/reset-password.html.twig',
            array(
                'user' => $user,
                'toast' => $toast,
                'form' => $form->createView()
            )
        );
    }
}
