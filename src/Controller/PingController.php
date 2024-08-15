<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class PingController extends AbstractController
{
    #[Route('/ping', methods: [Request::METHOD_GET], name: 'index')]
    public function ping(): JsonResponse
    {
        return new JsonResponse(['status' => 'pong']);
    }
}
