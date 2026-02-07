<?php

namespace App\Controller\Admin\CrudController\ClientSession;

use App\Enum\ClientSessionStatus;
use App\Form\Crud\MessageFormType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ClosedClientSessionCrudController extends AbstractClientSessionCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Closed Session')
            ->setEntityLabelInPlural('Closed Sessions')
            ->setSearchFields(['name', 'phone'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb
            ->andWhere('entity.closedAt IS NOT NULL')
            ->andWhere('entity.status = :closedStatus')
            ->setParameter('closedStatus', ClientSessionStatus::CLOSED->value);

        return $qb;
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

        yield TextField::new('externalId')
            ->setLabel('Session id')
            ->setColumns('col-md-8')
            ->setDisabled()
            ->hideOnIndex();

        yield CollectionField::new('messages')
            ->setFormTypeOption('entry_type', MessageFormType::class)
            ->setFormTypeOption('disabled', true)
            ->hideOnIndex();
    }

    #[IsGranted('ROLE_ADMIN or ROLE_EDITOR')]
    public function configureActions(Actions $actions): Actions
    {
        return
            $actions
                ->add(CRUD::PAGE_INDEX, 'detail')
                ->disable('new')
                ->disable('delete')
        ;
    }
}
