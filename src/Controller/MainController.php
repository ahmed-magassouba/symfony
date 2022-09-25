<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index( CategorieRepository $categorieRepository , ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy([], ['created_at' => 'DESC'], 8);

        return $this->render('main/index.html.twig',[
            'categories'=>$categorieRepository->findBy([],['parent'=>'ASC','name'=>'ASC']),
            'articles'=>$articles
        ]);
    }
    
}
