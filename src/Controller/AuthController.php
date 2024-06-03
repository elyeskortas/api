<?php

namespace App\Controller;

use App\Controller\ApiController;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

#[Route('', name: 'app_auth')]
class AuthController extends AbstractController
{
    private $emailService;
    private $passwordHasher;
    private $userRepository;
    private $apiController;
    private $entityManager;
    private $logger;

    public function __construct(
        EmailService $emailService,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        ApiController $apiController,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        // Dependency injection for various services and repositories
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->apiController = $apiController;
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
        $this->logger = $logger;
    }

    #[Route('/login', name: 'app_auth_login')]
public function login(Request $request, JWTTokenManagerInterface $JWTManager): Response
{
    // Log the request body for debugging
    $requestBody = $request->getContent();
    $this->logger->info('Request body: '.$requestBody);

    // Transform the JSON body of the request
    $request = $this->apiController->transformJsonBody($request);

    // Retrieve email and password from the request
    $email = $request->get('email');
    $password = $request->get('password');

    // Check if email or password is null
    if (empty($email) || empty($password)) {
        return new JsonResponse(['message' => 'Email and password are required.'], Response::HTTP_BAD_REQUEST);
    }

    // Find the user by email
    $user = $this->userRepository->findOneBy(['email' => $email]);

    // Check if the user exists
    if (!$user) {
        return $this->apiController->respondUnauthorized('User not found');
    }

    // Check if the entered password is valid
    if (!$this->passwordHasher->isPasswordValid($user, $password)) {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->apiController->respondUnauthorized('Invalid credentials');
    }

    // Check if the user's password needs to be reset
    if ($user->getPasswordResetRequired()) {
        // Set a flag to indicate that the user is required to reset their password
        $this->session->set('password_reset_required', true);

        // Redirect the user to the reset password route
        return $this->redirectToRoute('app_reset_password');
    }

    // Generate JWT token and update user login information
    $token = $JWTManager->create($user);
    $user->setLastLogin(new \DateTime());
    $user->setLoginCount($user->getLoginCount() + 1);
    $this->entityManager->persist($user);
    $this->entityManager->flush();

    // Prepare user data to respond with
    $userData = $this->userRepository->getUser($user);

    // Respond with success and user information
    $userData += [
        'token' => $token
    ];
    return $this->apiController->respondWithSuccess($userData);
}

    #[Route('/api/forgot_password', name: 'app_auth_forgot_password')]
    public function forgotPasswordEmail(EntityManagerInterface $entityManager, Request $request): Response
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
    
        // Generate reset URL
        $resetUrl = 'https://127.0.0.1:8000/reset_password/?token=' . $token;
    
        // Send email to the user for password change
        $subject = 'Forgot Password';
        $body = $this->renderView('send-to-update-password.html.twig', [
            'resetUrl' => $resetUrl,
            'user' => $user,
        ]);
        $this->emailService->sendEmail($email, $subject, $body);
    
        return $this->apiController->respondWithSuccess('Email sent to user for changing password');
    }
    
    #[Route('/api/reset_password', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(Request $request)
    {
        // Handle password reset functionality
    
        $user = null;
        $toast = null;
        $token = $request->query->get('token'); // Get token from the query string
    
        // Check if a token is present in the request
        if ($token) {
            $user = $this->userRepository->findOneBy(['tokenUpdatePassword' => $token]); 
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
                        $subject = 'Your password has been changed';
                        $body =  $this->renderView('update-password.html.twig', [
                            'user' => $user,
                        ]);
                        $this->emailService->sendEmail($user->getEmail(), $subject, $body);
                    }
                }
            }
        } else {
            // If token is not provided, return an error response
            return new Response('Token not provided', Response::HTTP_BAD_REQUEST);
        }
    
        // Render the reset password form
        return $this->render(
            'reset_password.html.twig',
            array(
                'user' => $user,
                'toast' => $toast,
                'form' => $form->createView(), // Pass the form to the template
                'token' => $token, // Pass the token to the template
            )
        );
    }}