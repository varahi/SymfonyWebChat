<?php

namespace App\Controller\Admin\CrudController\User;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractUserCrudController extends AbstractCrudController
{
    protected const ROLE_EDITOR = 'ROLE_EDITOR';

    protected const ROLE_ADMIN = 'ROLE_ADMIN';

    public function __construct(
        protected readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('messages'))
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('User')
            ->setSearchFields(['email', 'username'])
            ->setDefaultSort(['id' => 'DESC']);
    }
}
