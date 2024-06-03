<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private $validator;
    private $entityManager;
    private $serializer;
    private $passwordHasher;

    public function __construct(ManagerRegistry $registry, ValidatorInterface $validator, 
    EntityManagerInterface $entityManager, SerializerInterface $serializer,UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($registry, User::class);
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Get all users
     *
     * @return User[]
     */
    public function getAllUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get one user by ID
     *
     * @param int $id
     * @return User|array
     */
    public function getOne(int $id)
    {
        // Find the user by ID
        $user = $this->find($id);

        // Check if the user exists
        if (!$user) {
            // If the user is not found, return a not found response
            return $this->apiController->respondNotFound('User not found');
        }

        // If the user is found, return the user data
        return $this->apiController->respondWithSuccess($user);
    }

    /**
     * Validate user data
     *
     * @param Request $request
     * @param string $operation
     * @param int|null $id
     * @return array
     */
    public function validateData(Request $request, string $operation,?int $id = null): array
{
    $data = $request->request->all();

    $constraints = new Collection([
        'email' => [
            new Assert\Email(),
            new Assert\NotBlank(),
        ],
        'username' => [
            new Assert\NotBlank(),
        ],
    ], [
        'extraFieldsMessage' => 'This field is not expected.',
        'groups' => ['Default'],
    ]);

    // Validation for adding a new user
    if ($operation === 'add') {
        $constraints->add('password', new Assert\NotBlank([
            'groups' => ['Default'],
        ]));
        // Add more constraints for adding a user if needed
    }

    // Validation for updating a user
    if ($operation === 'update') {
        $constraints->add('password', new Assert\Optional([
            'groups' => ['Default'],
            new Assert\NotBlank(),
        ]));
        // Add more constraints for updating a user if needed
    }

    $violations = $this->validator->validate($data, $constraints);

    $errors = [];
    foreach ($violations as $violation) {
        $errors[] = $violation->getMessage();
    }

    return $errors;
}


public function setData(Request $request, ?string $photo = null): User
{
    $data = $request->request->all();

    $user = new User();
    $user->setEmail($data['email']);
    $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
    $user->setFirstName($data['firstName'] ?? null); // Set first name
    $user->setLastName($data['lastName'] ?? null); // Set last name
    $user->setPhoneNumber($data['phone_number'] ?? ''); // Set phone number or default to empty string
    $user->setIsActive(true); // Set is active to true by default
    $user->setCreatedAt(new \DateTimeImmutable()); // Set created_at to current time
    // Set other properties as needed

    if ($photo) {
        $user->setPhoto($photo);
    }

    // Persist the entity
    $this->entityManager->persist($user);
    $this->entityManager->flush();

    return $user;
}

public function updateData(Request $request, ?string $photo = null, int $id): User
{
    $user = $this->find($id);
    if (!$user) {
        throw new \InvalidArgumentException('User not found');
    }

    $data = $request->request->all();

    $user->setEmail($data['email'] ?? $user->getEmail());
    $user->setPassword($this->passwordHasher->hashPassword($user, $data['password'])); // Set password
    $user->setFirstName($data['firstName'] ?? $user->getFirstName()); // Set first name
    $user->setLastName($data['lastName'] ?? $user->getLastName()); // Set last name
    $user->setPhoneNumber($data['phone_number'] ?? $user->getPhoneNumber() ?? ''); // Set phone number or default to empty string
    $user->setIsActive(true); // Ensure is active is set
    // Update other properties as needed

    if ($photo) {
        $user->setPhoto($photo);
    }

    // Persist the entity
    $this->entityManager->flush();

    return $user;
}
public function getUser(User $user): array
{
    return [
        'id' => $user->getId(),
        'email' => $user->getEmail(),
        // Add other properties as needed
    ];
}

}