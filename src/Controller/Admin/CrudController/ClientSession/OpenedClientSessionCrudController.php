<?php

namespace App\Controller\Admin\CrudController\ClientSession;

use App\Entity\ClientSession;
use App\Enum\ClientSessionStatus;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OpenedClientSessionCrudController extends AbstractClientSessionCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->andWhere('entity.status = :status')
            ->setParameter('status', ClientSessionStatus::OPENED->value)
            ->orWhere('entity.status = :status')
            ->setParameter('status', ClientSessionStatus::OPERATOR_STARTED->value);

        return $qb;
    }

    #[IsGranted('ROLE_ADMIN or ROLE_EDITOR')]
    public function configureActions(Actions $actions): Actions
    {
        $openChat = Action::new('openChat', 'Открыть чат')
            ->setIcon('fas fa-comment')
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
}
