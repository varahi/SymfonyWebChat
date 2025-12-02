<?php

namespace App\Controller\Admin\Provider;

use App\Controller\Admin\CrudController\ClientSessionCrudController;
use App\Controller\Admin\Provider\Interface\MenuProviderInterface;
use App\Entity\ClientSession;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

class ClientSessionMenuProvider implements MenuProviderInterface
{
    public function getItems(): iterable
    {
        yield MenuItem::subMenu('Client Sessions', 'fas fa-user-alt')
            ->setSubItems([
                MenuItem::linkToCrud('Client Session', 'fas fa-user-circle', ClientSession::class)
                    ->setController(ClientSessionCrudController::class),
            ]);
    }
}
