<?php

namespace App\Controller\Admin;

use App\Controller\Admin\CrudController\FaqCrudController;
use App\Controller\Admin\Provider\ClientSessionMenuProvider;
use App\Controller\Admin\Provider\FaqMenuProvider;
use App\Controller\Admin\Provider\MainMenuProvider;
use App\Controller\Admin\Provider\MessageMenuProvider;
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
        private readonly FaqMenuProvider $faqMenuProvider,
        private readonly MessageMenuProvider $messageMenuProvider,
        private readonly ClientSessionMenuProvider $clientSessionMenuProvider
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
        yield from $this->messageMenuProvider->getItems();
        yield from $this->clientSessionMenuProvider->getItems();
    }
}
