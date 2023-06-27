<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validation;

#[Route('/api/users', name: 'app_user_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository              $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface      $entityManager
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        
        // TODO: Utiliser un normalizer pour ne pas exposer le mot de passe

        return $this->json($users);


    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $username = $content['username'];
        $password = $content['password'];

        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            return $this->json([
                'message' => 'Username or password not found'
            ], 404);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->json([
                'message' => 'Username or password not found'
            ], 400);
        }

        return $this->json([
            'user' => $user
        ]);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request)
    {
        // create a user using validator on the request content (json)
        $validator = Validation::createValidator();

        $content = json_decode($request->getContent(), true);

        $violations = $validator->validate($content, [
            'username' => new Required(),
            'password' => new Required([
                new Length(['min' => 8])
            ]),
            'roles' => new Required(),
            'name' => new Required()
        ]);

        if (count($violations) > 0) {
            return $this->json([
                'message' => 'Invalid data',
                'errors' => $violations
            ], 400);
        }

        $user = $this->userRepository->findOneBy(['username' => $content['username']]);

        if ($user) {
            return $this->json([
                'message' => 'Username already exists'
            ], 400);
        }

        $user = new User();
        $user->setUsername($content['username']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $content['password']));
        $user->setRoles([$content['role']]);
        $user->setName($content['name']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'User created'
        ], 201);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'User deleted'
        ]);
    }
}
