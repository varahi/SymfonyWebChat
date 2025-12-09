<?php

namespace App\Controller\Admin\Provider;

use App\Controller\Admin\CrudController\User\AdminCrudController;
use App\Controller\Admin\CrudController\User\OperatorCrudController;
use App\Controller\Admin\Provider\Interface\MenuProviderInterface;
use App\Entity\Message;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

class UserMenuProvider implements MenuProviderInterface
{
    public function getItems(): iterable
    {
        yield MenuItem::subMenu('Users', 'fas fa-user')
            ->setSubItems([
                MenuItem::linkToCrud('Admins', 'fas fa-user-plus', Message::class)
                    ->setController(AdminCrudController::class)
                    ->setPermission('ROLE_ADMIN'),

                MenuItem::linkToCrud('Operators', 'far fa-user', Message::class)
                    ->setController(OperatorCrudController::class)
                    ->setPermission('ROLE_ADMIN'),
            ]);
    }
}
