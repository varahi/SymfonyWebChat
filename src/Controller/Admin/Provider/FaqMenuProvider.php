<?php

namespace App\Controller\Admin\Provider;

use App\Controller\Admin\CrudController\FaqCrudController;
use App\Controller\Admin\Provider\Interface\MenuProviderInterface;
use App\Entity\Faq;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

class FaqMenuProvider implements MenuProviderInterface
{
    public function getItems(): iterable
    {
        yield MenuItem::subMenu('Faqs', 'fa fa-question-circle')
            ->setSubItems([
                MenuItem::linkToCrud('Faq list', 'fa fa-question', Faq::class)
                    ->setController(FaqCrudController::class)
                    ->setPermission('ROLE_ADMIN'),
            ]);
    }
}
