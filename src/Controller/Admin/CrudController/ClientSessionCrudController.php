<?php

namespace App\Controller\Admin\CrudController;

use App\Entity\ClientSession;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClientSessionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClientSession::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TelephoneField::new('phone'),
            TextField::new('name'),
            DateTimeField::new('createdAt')->setColumns('col-md-8')->setDisabled(),
            TelephoneField::new('externalId')
                ->setColumns('col-md-8')
                ->setDisabled()
                ->hideOnIndex(),
        ];
    }
}
