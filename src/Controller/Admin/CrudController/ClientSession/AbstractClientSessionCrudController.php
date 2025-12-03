<?php

namespace App\Controller\Admin\CrudController\ClientSession;

use App\Entity\ClientSession;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AbstractClientSessionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClientSession::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $openChat = Action::new('openChat', 'Открыть чат')
            ->linkToUrl(function (ClientSession $session) {
                return '/admin/client-session/'.$session->getId().'/chat';
            })
            ->setCssClass('btn btn-primary')
            ->setHtmlAttributes([
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ]);

        return $actions
            ->add(Crud::PAGE_INDEX, $openChat);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TelephoneField::new('phone');
        yield TextField::new('name');
        yield DateTimeField::new('createdAt')->setColumns('col-md-8')->setDisabled();
        yield TextField::new('externalId')
            ->setColumns('col-md-8')
            ->setDisabled()
            ->hideOnIndex();
    }
}
