<?php

namespace App\Controller\Admin\CrudController\User;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OperatorCrudController extends AbstractUserCrudController
{
    public const PERMISSION_EDIT_PASSWORD = 'is_granted("ROLE_ADMIN") or is_granted("ROLE_EDITOR")';

    public function __construct(
        TranslatorInterface $translator,
        private UserPasswordHasherInterface $passwordEncoder
    ) {
        parent::__construct($translator);
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);

        $title = $this->translator->trans('Operators', [], 'messages');

        return $crud
            ->setEntityLabelInSingular($this->translator->trans('List of', [], 'messages').' '.$title)
            ->setEntityLabelInPlural($this->translator->trans('List of', [], 'messages').' '.$title);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $roleAdmin = self::ROLE_EDITOR;

        if (isset($_GET['query'])) {
            $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        } else {
            $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
            $qb->where('entity.roles LIKE :roles');
            $qb->setParameter('roles', '%"'.$roleAdmin.'"%');
        }

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->setDisabled(true);
        yield TextField::new('email', 'Email');
        yield TextField::new('username', 'Username');

        yield Field::new('password', 'New password')->onlyWhenCreating()->setRequired(true)
            ->setFormType(RepeatedType::class)
            ->setRequired(false)
            ->setColumns('col-md-4')
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat password'],
                'error_bubbling' => true,
                'invalid_message' => 'The password fields do not match.',
            ])
            ->setPermission(self::PERMISSION_EDIT_PASSWORD);

        yield Field::new('password', 'New password')->onlyWhenUpdating()->setRequired(false)
            ->setFormType(RepeatedType::class)
            ->setRequired(false)
            ->setColumns('col-md-4')
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat password'],
                'error_bubbling' => true,
                'invalid_message' => 'The password fields do not match.',
            ])
            ->setPermission(self::PERMISSION_EDIT_PASSWORD);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $plainPassword = '';
        if (null !== $entityDto->getInstance()) {
            $plainPassword = $entityDto->getInstance()->getPassword();
        }

        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $this->addEncodePasswordEventListener($formBuilder, $plainPassword);

        return $formBuilder;
    }

    protected function addEncodePasswordEventListener(FormBuilderInterface $formBuilder, string $plainPassword = null): void
    {
        $formBuilder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($plainPassword) {
            /** @var User $user */
            $user = $event->getData();
            if ($user->getPassword() !== $plainPassword) {
                $user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPassword()));
            }
        });
    }
}
