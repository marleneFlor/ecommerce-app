<?php

declare(strict_types=1);

namespace App\Users\UI;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetUsersController
{
    public function __invoke(Request $request): JsonResponse
}