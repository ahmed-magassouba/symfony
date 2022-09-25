<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Image;
use App\Form\ArticleType;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article')]
class ArticleController extends AbstractController
{
    //Liste des aryticles
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $data = $articleRepository->findBy([], ['created_at' => 'DESC']);

        $articles = $paginator->paginate(
            $data, //On passe les données
            $request->query->getInt('page', 1), //Numéro de la page en cours, 1 par défaut
            10  //Nombre d'éléments par page
        );

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    // Créé un article
    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On recupère les images transmises
            $images = $form->get('image')->getData();
            // On boucle sur les images
            foreach ($images as $image) {
                // On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                // On copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                // On stocke l'image dans la bdd (son nom)
                $img = new Image();
                $img->setName($fichier);
                $article->addImage($img);
            }

            $article->setUser($this->getUser());
            $articleRepository->add($article, true);

            //  $entityManager->persist($article);
            //  $entityManager->flush();

            $this->addFlash('success', 'Article ajouté avec succès');

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    // Affiche un article
    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    // Edite un article
    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On recupère les images transmises
            $images = $form->get('image')->getData();
            // On boucle sur les images
            foreach ($images as $image) {
                // On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                // On copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                // On stocke l'image dans la bdd (son nom)
                $img = new Image();
                $img->setName($fichier);
                $article->addImage($img);
            }

            // $article->setUser($this->getUser());
            $articleRepository->add($article, true);


            //  $entityManager->persist($article);
            //  $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    // Supprime un article
    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $articleRepository->remove($article, true);
            $this->addFlash('success', 'Article supprimé avec succès');
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }


    // Affiche les articles d'un utilisateur
    #[Route('/user/{id}', name: 'app_article_user', methods: ['GET'])]
    public function user(ArticleRepository $articleRepository, PaginatorInterface $paginator, Request $request, $id): Response
    {
        $data = $articleRepository->findBy(['user' => $id], ['created_at' => 'DESC']);

        $articles = $paginator->paginate(
            $data, //On passe les données
            $request->query->getInt('page', 1), //Numéro de la page en cours, 1 par défaut
            10  //Nombre d'éléments par page
        );

        return $this->render('article/user.html.twig', [
            'articles' => $articles,
        ]);
    }

    // supprime une image
    #[Route('/image/{id}', name: 'app_article_image_delete')]
    public function deleteImage(Request $request, Image $image, ArticleRepository $articleRepository, EntityManagerInterface $entityManager ): Response
    {
dd("appel de la fonction reussi");
        $data = json_decode($request->getContent(), true);
dd("test");
        //On verifie si le token est valide
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $request->request->$data['_token'])) {

            //On recupère le nom de l'image
            $nom = $image->getName();

            //On supprime le fichier
            unlink($this->getParameter('images_directory') . '/' . $nom);

            //On supprime l'entrée de la base
            // $articleRepository->removeImage($image, true);
            dd("niveau 1");
            $entityManager->remove($image);
            $entityManager->flush();

            //On répond en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
