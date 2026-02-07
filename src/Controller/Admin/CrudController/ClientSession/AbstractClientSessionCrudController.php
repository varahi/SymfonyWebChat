<?php

namespace App\Controller\Admin\CrudController\ClientSession;

use App\Entity\ClientSession;
use App\Form\Crud\MessageFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AbstractClientSessionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClientSession::class;
    }

    //    public function configureFilters(Filters $filters): Filters
    //    {
    //        return $filters
    //            ->add(EntityFilter::new('messages'))
    //        ;
    //    }

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

    //    #[IsGranted('ROLE_ADMIN or ROLE_EDITOR')]
    //    public function configureActions(Actions $actions): Actions
    //    {
    //                $openChat = Action::new('openChat', 'Открыть чат')
    //                    ->setIcon('fas fa-comment')
    //                    ->linkToUrl(function (ClientSession $session) {
    //                        return '/admin/client-session/'.$session->getId().'/chat';
    //                    })
    //                    ->setCssClass('btn btn-primary')
    //                    ->setHtmlAttributes([
    //                        'target' => '_blank',
    //                        'rel' => 'noopener noreferrer',
    //                    ]);
    //
    //                return $actions
    //                    ->add(Crud::PAGE_INDEX, $openChat)
    //                    ->disable('new');
    //
    //    }
}
