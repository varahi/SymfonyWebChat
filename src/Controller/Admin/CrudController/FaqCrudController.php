<?php

namespace App\Controller\Admin\CrudController;

use App\Entity\Faq;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute('/faq', name: 'faq_')]
class FaqCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Faq::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('question')->setLabel('Question'),
            ArrayField::new('pattern')
                ->setLabel('Pattern')
                ->hideOnIndex(),

            TextareaField::new('answer')->setLabel('Answer'),
        ];
    }
}
