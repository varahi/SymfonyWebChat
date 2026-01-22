<?php

namespace App\Controller\Admin\Provider;

use App\Controller\Admin\CrudController\ClientSession\ClosedClientSessionCrudController;
use App\Controller\Admin\CrudController\ClientSession\OpenedClientSessionCrudController;
use App\Controller\Admin\Provider\Interface\MenuProviderInterface;
use App\Entity\ClientSession;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

class ClientSessionMenuProvider implements MenuProviderInterface
{
    public function getItems(): iterable
    {
        yield MenuItem::subMenu('Client Sessions', 'fas fa-user-alt')
            ->setSubItems([
                MenuItem::linkToCrud('Opened Session', 'fas fa-comment', ClientSession::class)
                    ->setController(OpenedClientSessionCrudController::class),
//
//                MenuItem::linkToCrud('Closed Session', 'fas fa-user-circle', ClientSession::class)
//                    ->setController(ClosedClientSessionCrudController::class),
            ]);
    }
}
