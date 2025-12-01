<?php

namespace App\Controller\Admin\Provider;

use App\Controller\Admin\FaqCrudController;
use App\Controller\Admin\Provider\Interface\FaqMenuProviderInterface;
use App\Entity\Faq;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

class FaqMenuProvider implements FaqMenuProviderInterface
{

    public function getItems(): iterable
    {
        yield MenuItem::subMenu('Faqs', 'fa fa-question-circle')
            ->setSubItems([
                MenuItem::linkToCrud('Faq list', 'fa fa-question', Faq::class)
                    ->setController(FaqCrudController::class),
            ]);
    }
}
