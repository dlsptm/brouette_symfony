<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UsersAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMailService $mailService, JWTService $jwt): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();


            // do anything else you need here, like send an email

            // on génère le JWT de l'utilisateur
            // on crée le header
            $header = [
                'typ' => 'jwt',
                'alg' => 'hS256'
            ];

            $payload = [
                'user_id' => $user->getId()
            ];

            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            $mailService->send(
                'dlsptm6981@gmail.com', 
                $user->getEmail(), 
                'Activation de votre compte sur le site E-commerce', 
                'register', 
                compact('user', 'token')
            );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verification/{token}', name: 'verify_user')]
     public function verification ($token, JWTService $jwt, UsersRepository $usersrepo, EntityManagerInterface $manager):Response
    {   

        // on vérifie si ke Token est valide, n'a pas expiré et n'a pas été modifé
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret')))
        {
            // je réccupère le payload
            $payload = $jwt->getPayLoad($token);

            // on réccupère le user du token
            $user = $usersrepo->find($payload['user_id']);

            // on vérifie que l'utilisateur existe et n'a pas encore activé son compte
            if ($user && !$user->getIsVerified())
            {
                $user->setIsVerified(true);
                $manager->flush($user);
                $this->addFlash('success', 'Utilisaeur activé');
                return $this->redirectToRoute('app_profilapp_index');

            }
        }

        // ici un probleme se pose dans le token 
        $this->addFlash('danger', 'Le token est invalide ou a expiré');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/resend/verif', name: 'resend_verif')]
     public function resendVerif ( JWTService $jwt, UsersRepository $usersrepo, EntityManagerInterface $manager, SendMailService $mailService):Response
    {
        $user = $this->getUser();

        if(!$user)
        {
            $this->addFlash('danger','Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        if ($user->getIsVerified())
        {
            $this->addFlash('warning','Cet utilisateur est déjà activé');
            return $this->redirectToRoute('app_profilapp_index');
        }

        $header = [
            'typ' => 'jwt',
            'alg' => 'hS256'
        ];

        $payload = [
            'user_id' => $user->getId()
        ];

        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        $mailService->send(
            'dlsptm6981@gmail.com', 
            $user->getEmail(), 
            'Activation de votre compte sur le site E-commerce', 
            'register', 
            compact('user', 'token')
        );

        return $this->redirectToRoute('app_profilapp_index');

    }
}
