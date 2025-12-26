<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, UserRepository $userRepository): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Determine a friendly, specific error message according to the submitted email and existence
        $loginErrorMessage = null;
        if ($error) {
            // 1) invalid email format
            if ($lastUsername && !filter_var($lastUsername, FILTER_VALIDATE_EMAIL)) {
                $loginErrorMessage = 'Email non valide';
            } elseif ($lastUsername) {
                // 2) email is syntactically valid — check if it exists in DB
                $user = $userRepository->findOneBy(['email' => $lastUsername]);
                if (!$user) {
                    $loginErrorMessage = 'Adresse email incorrecte';
                } else {
                    // 3) user exists => password likely incorrect
                    $loginErrorMessage = 'Mot de passe incorrecte';
                }
            } else {
                // Fallback generic message
                $loginErrorMessage = 'Identifiants invalides';
            }
        }

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'login_error_message' => $loginErrorMessage]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
