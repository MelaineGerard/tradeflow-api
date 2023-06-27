<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Repository\PrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validation;

#[Route('/api/prestations', name: 'app_prestations_')]
class PrestationController extends AbstractController
{

    public function __construct(
        private readonly PrestationRepository        $prestationRepository,
        private readonly EntityManagerInterface      $entityManager
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(): JsonResponse
    {
        $prestations = $this->prestationRepository->findAll();


        return $this->json($prestations);


    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    #[Route('/edit/{id}', name: 'edit', methods: ['PUT'])]
    public function create(?Prestation $prestation, Request $request): JsonResponse
    {
        if(!$prestation instanceof Prestation) {
            $prestation = new Prestation();
        }
        $validator = Validation::createValidator();
        $content = json_decode($request->getContent(), true);

        $violations = $validator->validate($content, [
            'name' => new Required(),
            'price' => new Required(),
        ]);

        if (count($violations) > 0) {
            return $this->json([
                'message' => 'Invalid data',
                'errors' => $violations
            ], 400);
        }

        $prestation->setName($content['name']);
        $prestation->setPrice($content['price']);

        $this->entityManager->persist($prestation);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Prestation created or updated'
        ], 201);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Prestation $prestation): JsonResponse
    {
        $this->entityManager->remove($prestation);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Client deleted'
        ]);
    }
}
