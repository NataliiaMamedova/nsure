<?php

declare(strict_types=1);

namespace App\Controller\API\V1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/public/nsure-service/v1/rand', methods: ['GET'])]
    public function defaultAction(): JsonResponse
    {
        return $this->json([
            'test' => mt_rand(),
        ]);
    }
}
