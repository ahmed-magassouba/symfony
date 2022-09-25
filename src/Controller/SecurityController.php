<?php

namespace App\Controller;

use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Stmt\TryCatch;
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
        if ($this->getUser()) {
            return $this->redirectToRoute('main');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    // Recuperation du mot de passe
    #[Route(path: '/oubli-pass', name: 'forgotten_password')]
    public function forgottenPassword(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGeneratorInterface, EntityManagerInterface $entityManagerInterface, SendMailService $sendMail): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On va chercher l'utilateur correspondant a cet email
            $user = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);

            // Si l'utilisateur n'existe pas
            if (!$user) {
                $this->addFlash('danger', 'Cette adresse email n\'existe pas');
                return $this->redirectToRoute('app_login');
            }

            // On a un utilisateur, on génère un token de régeneration
            $token = $tokenGeneratorInterface->generateToken();

            // On enregistre le token en base de données
            $user->setResetToken($token);
            $entityManagerInterface->persist($user);

            try {
                $entityManagerInterface->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('app_login');
            }


            // On envoie un email à l'utilisateur avec un lien lui permettant de choisir un nouveau mot de passe
            $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            // On crée les données du mail
            $context = [
                'url' => $url,
                'user' => $user,
            ];

            // On envoie le mail
            try {
                $sendMail->sendMail(
                    'noreply@monsite.net',
                    $user->getEmail(),
                    'Réinitialisation de votre mot de passe',
                    'resetpassword',
                    $context
                );
                $this->addFlash('success', 'Un email vous a été envoyé pour réinitialiser votre mot de passe');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('app_login');
            }

        }

        return $this->render('security/forgotten_password.html.twig', [
            'requestPassForm' => $form->createView(),
        ]);
    }



    // Recuperation du mot de passe
    #[Route(path: '/oubli-pass/{token}', name: 'reset_password')]
    public function resetPass(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManagerInterface,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {

        // On verifie si on a un token correspondant dans la base de données
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('danger', 'Votre lien de réinitialisation de mot de passe est invalide ou a expiré');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($form->get('mdp')->getData() !== $form->get('confirm_mdp')->getData()) {
                $this->addFlash('danger', 'Les mots de passe ne correspondent pas');
                return $this->redirectToRoute('reset_password', ['token' => $token]);
            }

            // On efface le token
            $user->setResetToken(null);
            // On hash le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('mdp')->getData()
                )
            );

            // On enregistre le tout en base de données
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            // On envoi un message et on redirrige vers la page de connexion
            $this->addFlash('success', 'Votre mot de passe a bien été modifié');
            return $this->redirectToRoute('app_login');
        }


        return $this->render('security/reset_password.html.twig', [
            'resetPassForm' => $form->createView(),
        ]);
    }
}
