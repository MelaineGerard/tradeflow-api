<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validation;

#[Route('/api/clients', name: 'app_client_')]

class ClientController extends AbstractController
{
    public function __construct(
        private readonly ClientRepository            $clientRepository,
        private readonly EntityManagerInterface      $entityManager
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(): JsonResponse
    {
        $clients = $this->clientRepository->findAll();


        return $this->json($clients);


    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    #[Route('/edit/{id}', name: 'edit', methods: ['PUT'])]
    public function create(?Client $client, Request $request): JsonResponse
    {
        if(!$client instanceof Client) {
            $client = new Client();
        }
        $validator = Validation::createValidator();
        $content = json_decode($request->getContent(), true);

        $violations = $validator->validate($content, [
            'name' => new Required(),
            'address' => new Required(),
            'city' => new Required(),
        ]);

        if (count($violations) > 0) {
            return $this->json([
                'message' => 'Invalid data',
                'errors' => $violations
            ], 400);
        }

        $client->setName($content['name']);
        $client->setAddress($content['address']);
        $client->setCity($content['city']);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Client created or updated'
        ], 201);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Client $client): JsonResponse
    {
        $this->entityManager->remove($client);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Client deleted'
        ]);
    }
}
