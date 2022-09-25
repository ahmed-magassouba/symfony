<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UserAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMailService $sendMail, JWTService $jwt): Response
    {
        $user = new User();
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



            //On génère le token
            //On cree le header
            $header = [
                'alg' => 'HS256',
                'typ' => 'JWT'
            ];
            //On cree le payload
            $payload = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles()
            ];
            //On genere le token
            $token = $jwt->generateToken($header, $payload, $this->getParameter('app.jwtsecret'));

            // On envoie un mail de confirmation
            $sendMail->sendMail(
                "no-reply@monsite.net",
                $user->getEmail(),
                'Inscrition et activation de votre compte',
                'register',
                [
                    'user' => $user,
                    'token' => $token
                ]
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


    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // On vérifie si le token est valide , n'as pas expiré et qu'il correspond à un utilisateur
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {

            // On récupère le payload du token
            $payload = $jwt->getPayload($token);

            // On récupère le user liée au token
            $user = $userRepository->find($payload['id']);

            // On vérifie que l'utilisateur existe et qu'i n'a pas encore activé son compte
            if ($user && !$user->getIsVerified()) {

                // On active le compte
                $user->setIsVerified(true);

                // On enregistre les modifications
                $entityManager->persist($user);
                $entityManager->flush();

                // On affiche un message de succès
                $this->addFlash('success', 'Votre compte a bien été activé');

                // On redirige vers la page de connexion
                return $this->redirectToRoute('profile_index');
            }


            // On redirige vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Ici on peut afficher un message d'erreur
        $this->addFlash('danger', 'Le lien de vérification est invalide ou a expiré');

        return $this->redirectToRoute('app_login');
    }


    #[Route('/renvoiVerif', name: 'resend_verif')]
    public function resendVerif(JWTService $jwt, SendMailService $sendMail, UserRepository $userRepository): Response
    {
        // On récupère l'utilisateur connecté
        $user = $this->getUser();

        // si il n'est pas connecté on le redirige vers la page de connexion
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        // On vérifie que l'utilisateur n'a pas déjà activé son compte
        if ($user && $user->getIsVerified()) {
            $this->addFlash('warning', 'Votre compte est déjà activé');
            return $this->redirectToRoute('profile_index');
        }

        // On vérifie que l'utilisateur existe et qu'il n'a pas encore activé son compte
     
            //On génère le token
            //On cree le header
            $header = [
                'alg' => 'HS256',
                'typ' => 'JWT'
            ];
            //On cree le payload
            $payload = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles()
            ];
            //On genere le token
            $token = $jwt->generateToken($header, $payload, $this->getParameter('app.jwtsecret'));

            // On envoie un mail de confirmation
            $sendMail->sendMail(
                "no-reply@monsite.net",
                $user->getEmail(),
                'Inscrition et activation de votre compte',
                'register',
                [
                    'user' => $user,
                    'token' => $token
                ]
            );

            // On affiche un message de succès
            $this->addFlash('success', 'Un nouveau lien de vérification vous a été envoyé');
            return $this->redirectToRoute('profile_index');
        }
    
}
