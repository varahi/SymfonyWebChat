<?php

namespace App\Controller\Admin\Provider;

use App\Controller\Admin\CrudController\UserCrudController;
use App\Controller\Admin\Provider\Interface\MenuProviderInterface;
use App\Entity\Message;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

class UserMenuProvider implements MenuProviderInterface
{
    public function getItems(): iterable
    {
        yield MenuItem::subMenu('Users', 'fas fa-user')
            ->setSubItems([
                MenuItem::linkToCrud('Admins', 'far fa-user', Message::class)
                    ->setController(UserCrudController::class),
            ]);
    }
}
