<?php

declare(strict_types=1);

namespace App\Controller\Admin\CrudController\ClientSession;

use App\Form\Crud\MessageFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;

class AllSessionCrudController extends AbstractClientSessionCrudController
{
    public function __construct(
        private Security $security
    ) {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Session')
            ->setEntityLabelInPlural('All Sessions')
            ->setSearchFields(['name', 'phone'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id');
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

    public function configureActions(Actions $actions): Actions
    {
        $actions = $actions
            ->add(CRUD::PAGE_INDEX, 'detail')
            ->disable('new')
        ;

        // запрещаем delete только для Editor
        if ($this->security->isGranted('ROLE_EDITOR')) {
            $actions = $actions->disable('delete');
        }

        return $actions;
    }
}
