<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Dashboard\MainMenuProvider;
use App\Controller\Admin\Provider\MenuProvider;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(
    routePath: '/admin',
    routeName: 'admin'
)]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly MainMenuProvider $mainMenuProvider,
        private readonly MenuProvider $faqMenuProvider
    ) {
    }

    public function index(): Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($routeBuilder->setController(FaqCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Web Chat admin')
            ->renderContentMaximized()
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield from $this->mainMenuProvider->getMainMenu();
        yield from $this->faqMenuProvider->getItems();
    }
}
