<?php

namespace App\Controller\Admin\CrudController\ClientSession;

use App\Enum\ClientSessionStatus;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClosedClientSessionCrudController extends AbstractClientSessionCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        // $qb->andWhere('entity.closedAt IS NULL');
        $qb->andWhere('entity.status = :closedStatus')
            ->setParameter('closedStatus', ClientSessionStatus::CLOSED->value);

        return $qb;
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
