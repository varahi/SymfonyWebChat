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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OpenedClientSessionCrudController extends AbstractClientSessionCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->andWhere('entity.status IN (:statuses)')
            ->setParameter('statuses', [
                ClientSessionStatus::OPENED->value,
                ClientSessionStatus::OPERATOR_STARTED->value,
            ]);

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('phone');
        yield TextField::new('name');

        //        yield ChoiceField::new('status')
        //            ->setChoices([
        //                'Открыта' => ClientSessionStatus::OPENED->value,
        //                'Оператор подключился' => ClientSessionStatus::OPERATOR_STARTED->value,
        //                'Закрыта' => ClientSessionStatus::CLOSED->value,
        //            ])
        //            ->renderAsBadges([
        //                ClientSessionStatus::OPENED->value => 'warning',
        //                ClientSessionStatus::OPERATOR_STARTED->value => 'info',
        //                ClientSessionStatus::CLOSED->value => 'secondary',
        //            ])
        //            ->setDisabled();

        yield DateTimeField::new('createdAt')->setColumns('col-md-8')->setDisabled();

        yield Field::new('chat', 'Чат')
            ->onlyOnIndex()
            ->setSortable(false)
            ->setTemplatePath('admin/field/open_chat_button.html.twig');

        yield TextField::new('externalId')
            ->setLabel('Session id')
            ->setColumns('col-md-8')
            ->setDisabled()
            ->hideOnIndex();

        yield CollectionField::new('messages')
            ->setLabel('Messages')
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
                ->disable('edit')
                ->disable('detail')
                ->disable('delete')
        ;
    }
}
