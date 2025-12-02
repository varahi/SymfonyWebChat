<?php

namespace App\Controller\Admin\CrudController;

use App\Entity\ClientSession;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ClientSessionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClientSession::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
