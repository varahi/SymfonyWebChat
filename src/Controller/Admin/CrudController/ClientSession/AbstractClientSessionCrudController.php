<?php

namespace App\Controller\Admin\CrudController\ClientSession;

use App\Entity\ClientSession;
use App\Form\Crud\MessageFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class AbstractClientSessionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClientSession::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('messages'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('phone');
        yield TextField::new('name');

        yield DateTimeField::new('createdAt')->setColumns('col-md-8')->setDisabled();
        yield TextField::new('externalId')
            ->setColumns('col-md-8')
            ->setDisabled()
            ->hideOnIndex();

        yield CollectionField::new('messages')
            ->setFormTypeOption('entry_type', MessageFormType::class)
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex();
    }
}
