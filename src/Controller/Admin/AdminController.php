<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractDashboardController
{


   public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
        // ...
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // return parent::index();
        $url =$this->adminUrlGenerator->setController(CategorieCrudController::class)->generateUrl();
        return $this->redirect($url);

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Blog');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Blog');

        yield MenuItem::section('Articles');
        yield MenuItem::subMenu('Actions', 'fa fa-bars')->setSubItems([
            MenuItem::linkToCrud('Add Articles', 'fa fa-plus', Article::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Liste Articles', 'fa fa-eye', Article::class),
        ]);

        yield MenuItem::section('Categories');
        yield MenuItem::subMenu('Actions','fa fa-bars')->setSubItems([
            MenuItem::linkToCrud('Add Categories', 'fa fa-plus', Categorie::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Liste Categories', 'fa fa-eye', Categorie::class),
        ]);

        yield MenuItem::section('Utilisateur');
        yield MenuItem::subMenu('Actions','fa fa-bars')->setSubItems([
            MenuItem::linkToCrud('Ajouter un utilisateur', 'fa fa-plus', User::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Liste des utilisateur', 'fa fa-eye', User::class),
        ]);

        yield MenuItem::section('Commentaires');
        yield MenuItem::subMenu('Actions','fa fa-bars')->setSubItems([
            MenuItem::linkToCrud('Ajouter un commentaire', 'fa fa-plus', Comment::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Liste des commentaire', 'fa fa-eye', Comment::class),
        ]);

        yield MenuItem::section('Images');
        yield MenuItem::subMenu('Actions','fa fa-bars')->setSubItems([
            MenuItem::linkToCrud('Ajouter un images', 'fa fa-plus', Image::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Liste des images', 'fa fa-eye', Image::class),
        ]);

       

    }
}
