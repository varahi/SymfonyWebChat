<?php

namespace App\Controller\Admin;

use App\Controller\Admin\CrudController\ClientSession\OpenedClientSessionCrudController;
use App\Controller\Admin\Provider\ClientSessionMenuProvider;
use App\Controller\Admin\Provider\FaqMenuProvider;
use App\Controller\Admin\Provider\MainMenuProvider;
use App\Controller\Admin\Provider\MessageMenuProvider;
use App\Controller\Admin\Provider\UserMenuProvider;
use App\Entity\User;
use App\Service\Interface\UserServiceInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

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
        private readonly ClientSessionMenuProvider $clientSessionMenuProvider,
        private readonly UserMenuProvider $userMenuProvider,
        private readonly UserServiceInterface $userService
    ) {
    }

    public function index(): Response
    {
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($routeBuilder->setController(OpenedClientSessionCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Web Chat Panel')
            ->renderContentMaximized()
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield from $this->mainMenuProvider->getMainMenu();
        yield from $this->faqMenuProvider->getItems();
        yield MenuItem::section('<hr />');
        // yield from $this->messageMenuProvider->getItems();
        yield from $this->clientSessionMenuProvider->getItems();
        yield MenuItem::section('<hr />');
        yield from $this->userMenuProvider->getItems();
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $user = $this->userService->getUserByUsername($this->getUser());
        $userMenuItems = [
            MenuItem::linkToCrud('Profile', 'fa fa-tags', User::class)
                ->setAction('edit')
                ->setEntityId($user->getId()),
            MenuItem::linkToLogout('Logout', 'fa-sign-out'),
        ];

        return UserMenu::new()
            ->displayUserName()
            ->displayUserAvatar()
            ->setName(method_exists($user, '__toString') ? (string) $user : $user->getEmail())
            // ->setName($user->getFirstName() .$user->getLastName(). $user->getEmail())
            ->displayUserName(true)
            ->setAvatarUrl(null)
            ->setMenuItems($userMenuItems);
    }
}
