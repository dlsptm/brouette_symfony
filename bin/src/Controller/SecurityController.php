<?php

namespace App\Controller;

use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UsersRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/forget/password', name: 'forgotten_password')]
     public function forget_password (Request $request, UsersRepository $repo, TokenGeneratorInterface $token, EntityManagerInterface $manager, SendMailService $sendMailService ):Response
    {   

        $form = $this->createForm(ResetPasswordRequestType::class);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // on va chercher l'utilisateur par son email

            $user = $repo->findOneByEmail($form->get('email')->getData());

            if($user) 
            {
                // on utilise un token de réinitialisation 
                $token = $token->generateToken();
                $user->setResetToken($token);
                $user->setIsVerified(0);
                $manager->persist($user);
                $manager->flush();

                // On génére un lien de réiniliation du mot de passe
                $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                
                // on crée les données du mail
                $context = compact('url', 'user');

                //mail

                $sendMailService->send(
                    'dlsptm6981@gmail.com',
                    $user->getEmail(),
                    'Réinitialiser le mot de passe',
                    'password_reset',
                    $context
                );

                $this->addFlash('success', 'Email envoyé avec succès');
                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('danger', 'un problème est survenu');
            return $this->redirectToRoute('app_login');
        }

        
        return $this->render('security/forget_password.html.twig', [
            'requestResetPassword' => $form->createView()
        ]);
    }

    #[Route('/reset/password/{token}', name: 'reset_password')]
     public function reset_password (string $token, Request $request, UsersRepository $repo, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher):Response
    {   
        // on vérifie le token dans la base de donnée
        $user= $repo->findOneByResetToken($token);
        if ($user) {
            $form = $this->createForm(ResetPasswordType::class);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid())
            {
                //on efface le token
                $user->setResetToken('');
                $user->setPassword(
                    $hasher->hashPassword($user, $form->get('password')->getData()
                ));
                $user->setIsVerified(1);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Mot de passe réinitialisée');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/reset_password.html.twig', [
                'form' => $form->createView()
            ]);
        }

        $this->addFlash('danger', 'token invalide');
        return $this->redirectToRoute('app_login');
    }
}
