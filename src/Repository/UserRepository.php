<?php

namespace App\Repository;

use App\Controller\ApiController;
use App\Entity\User;
use App\Service\UserService;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * UserRepository
 *
 * This class extends the ServiceEntityRepository and implements the PasswordUpgraderInterface for the User entity.
 *
 * @extends ServiceEntityRepository<User>
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private object $passwordHasher;
    private object $apiController;
    private object $userService;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param ApiController $apiController
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(
        ManagerRegistry $registry,
        ApiController $apiController,
        UserService $userService,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->apiController = $apiController;
        $this->userService = $userService;
        parent::__construct($registry, User::class);
    }

    /**
     * Validate data from the request and create a new User instance.
     *
     * @param mixed $request
     * @return User
     */
    public function validateData($request, $action, $id = null)
    {
        switch ($action) {
            case 'add':
                $user = new User();
                break;
            case 'update':
                $user = $this->find($id);
                break;
        }
        if ($action === 'add' || ($action === 'update' && $user->getEmail() !== $request->get('email'))){
            $user->setEmail($request->get('email'));
        }
        $user->setFirstName($request->get('firstName'));
        $user->setLastName($request->get('lastName'));

        return $user;
    }

    /**
     * Set data from the request, generate a random password, and persist the User instance.
     *
     * @param mixed $request
     * @param mixed $photo
     * @return User
     */
    public function setData($request, $photo)
    {
  
        $user = new User();
        $this->setUserData($user, $request, $photo);
        $user->setEmail($request->get('email') ?? $user->getEmail());
        $user->setPassword($this->passwordHasher->hashPassword($user, $this->generateRandomPassword()));
        $user->setTokenUpdatePassword($this->apiController->generateToken());
        $user->setRoles(['ROLE_ADMIN']);
        $user->setIsActive(false);
        $user->setCreatedAt();
        $this->saveUser($user);

        return $user;
    }

    /**
     * Update user data based on the request, photo, and user ID.
     *
     * @param mixed $request
     * @param mixed $photo
     * @param int $id
     * @return User
     */
    public function updateData($request, $photo, $id)
    {
        
        $user = $this->find($id);
        $this->setUserData($user, $request, $photo);
        if ( $user->getEmail() !== $request->get('email')){
            $user->setEmail($request->get('email') ?? $user->getEmail());
        }
        $this->saveUser($user);
        
        $user->setUpdatedAt();
        return $user;
    }

    /**
     * Set user data based on the request, photo, and optional password.
     *
     * @param User $user
     * @param mixed $request
     * @param mixed $photo
     * @return void
     */
    private function setUserData(User $user, $request, $photo)
    {
        $user->setFirstName($request->get('firstName') ?? $user->getFirstName());
        $user->setLastName($request->get('lastName') ?? $user->getLastName());
        $user->setPhoneNumber($request->get('phoneNumber') ?? $user->getPhoneNumber());
        $user->setCity($request->get('city') ?? $user->getCity());
        $user->setStreet($request->get('street') ?? $user->getStreet());
        $user->setZipCode($request->get('zipCode') ?? $user->getZipCode());
        $user->setBirthdate($request->get('birthdate') ? DateTimeImmutable::createFromFormat('d/m/Y', $request->get('birthdate')) : $user->getBirthdate());
        $user->setPhoto($photo ?? $user->getPhoto());
    }

    /**
     * Activate or deactivate a user based on their ID.
     *
     * @param int $id
     * @return User
     */
    public function activeAndInactive($id)
    {
        $user = $this->find($id);
        $user->getIsActive() ? $user->setIsActive(false) :  $user->setIsActive(true);
        $user->setUpdatedAt();
        $this->saveUser($user);
        return $user;
    }

    /**
     * Update the token for updating the password and set the password requested timestamp.
     *
     * @param int $id
     * @return User
     */
    public function forgotPasswordEmail($id)
    {
        $user = $this->find($id);
        $user->setTokenUpdatePassword($this->apiController->generateToken());
        $user->setPasswordRequestedAt();
        $this->saveUser($user);
        return $user;
    }

    /**
     * update password user based on their ID.
     *
     * @param int $id
     * @return User
     */
    public function UpdatePassword($request, $id)
    {
        $user = $this->find($id);
        $user->setPassword($this->passwordHasher->hashPassword($user, $request->get('password')));
        $user->setUpdatedAt();
        $this->saveUser($user);
        return $user;
    }

    /**
     * Get an array representation of the user data.
     *
     * @param User $user
     * @return array
     */
    public function getUser($user)
    {
        return array(
            "id" =>  $user->getId(),
            "photo" =>  $user->getPhoto(),
            "email" =>  $user->getEmail(),
            "firstName" =>  $user->getFirstName(),
            "lastName" =>  $user->getLastName(),
            "birthdate" => $user->getBirthdate() ?  $user->getBirthdate()->format('d/m/Y') : null,
            "phoneNumber" =>  $user->getPhoneNumber(),
            "role" => $user->getRoles()[0],
            "city" =>  $user->getCity() ? $user->getCity() : null,
            "street" =>  $user->getStreet() ? $user->getStreet() : null,
            "zipCode" =>  $user->getZipCode() ? $user->getZipCode() : null,
            "isActive" => $user->getIsActive(),
            "createdAt" => $user->getCreatedAt() ? $user->getCreatedAt()->format('d/m/Y H:i:s') : null,
            "updatedAt" => $user->getUpdatedAt() ? $user->getUpdatedAt()->format('d/m/Y H:i:s') : null,
        );
    }

     /**
     * Get an array representation of the user data.
     *
     * @param User $user
     * @return array
     */
    public function getUserMinimize($user) {
        return array(
            "id" =>  $user->getId(),
            "email" =>  $user->getEmail(),
            "name" =>  $user->getLastName() . ' ' . $user->getFirstName(),
            "role" => $user->getRoles()[0],
        );
    }

    /**
     * Get an array representation of all user data.
     *
     * @return array
     */
    public function getAllUsers()
    {
        $users = $this->findAll();
        $data = [];
        foreach ($users as $user) {
            $data[] = $this->getUser($user);
        }
        return $data;
    }

    /**
     * Generate a random password.
     *
     * @return string
     */
    private function generateRandomPassword()
    {
        return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 10);
    }

    /**
     * Save user in database
     * 
     * @param User $user
     * @return User
     */
    private function saveUser($user)
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
        return $user;
    }
}
