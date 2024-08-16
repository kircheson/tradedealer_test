<?php

namespace App\Controller\v1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class PingController extends AbstractController
{
    #[Route('/ping', name: 'api_v1_ping', methods: [Request::METHOD_GET])]
    public function ping(): JsonResponse
    {
        return new JsonResponse(['status' => 'pong']);
    }
}
