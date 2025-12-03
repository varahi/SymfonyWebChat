<?php

namespace App\Controller\Admin\Provider;

use App\Controller\Admin\CrudController\Message\MessageCrudController;
use App\Controller\Admin\Provider\Interface\MenuProviderInterface;
use App\Entity\Message;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

class MessageMenuProvider implements MenuProviderInterface
{
    public function getItems(): iterable
    {
        yield MenuItem::subMenu('Messages', 'fas fa-comment')
            ->setSubItems([
                MenuItem::linkToCrud('Messages', 'far fa-comment', Message::class)
                    ->setController(MessageCrudController::class),
            ]);
    }
}
