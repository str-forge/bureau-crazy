<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Repository\UserRepository;
use App\Service\SecurityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private SecurityManager $securityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/api/v1/auth/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Invalid credentials.'], 401);
        }

        if ($user->isLocked()) {
            return new JsonResponse(['error' => 'Account is locked.'], 403);
        }

        if ($user->is2faEnabled()) {
            $preAuthToken = $this->securityManager->createJwtToken($user);
            return new JsonResponse([
                'requires_2fa' => true,
                'pre_auth_token' => $preAuthToken
            ], 200);
        }

        $accessToken = $this->securityManager->createJwtToken($user);
        return new JsonResponse([
            'requires_2fa' => false,
            'access_token' => $accessToken
        ], 200);
    }
}
