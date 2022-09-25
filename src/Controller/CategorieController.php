<?php

namespace App\Controller;

use App\Entity\Categorie;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categorie', name: 'categorie_')]
class CategorieController extends AbstractController
{
    #[Route('/{slug}', name: 'list')]
    public function index( Categorie $categorie, PaginatorInterface $paginator ,Request $request): Response
    {
//On va chercehr les articles de la catégorie
        $data = $categorie->getArticles();

        $articles = $paginator->paginate(
            $data,//On passe les données
            $request->query->getInt('page', 1),//Numéro de la page en cours, 1 par défaut
            5  //Nombre d'éléments par page
        );

        return $this->render('categorie/index.html.twig', [
            'categorie' => $categorie,
            'articles' => $articles
        ]);
    }
}
