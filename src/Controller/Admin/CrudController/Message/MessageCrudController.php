<?php

namespace App\Controller\Admin\CrudController\Message;

use App\Entity\Message;
use App\Enum\MessageRole;
use App\Enum\MessageStatus;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class MessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Message::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // IdField::new('id'),
            TextareaField::new('message'),
            ChoiceField::new('status')
                ->setChoices([
                    'Создано' => MessageStatus::CREATED,
                    'В процессе' => MessageStatus::PROCESSED,
                    'Отказано' => MessageStatus::REJECTED,
                    'Завершено' => MessageStatus::COMPLETED,
                ])
                ->allowMultipleChoices(false)
                ->renderExpanded(false)
                ->renderAsBadges()
                ->setFormTypeOption('choice_value', function (?MessageStatus $choice) {
                    return $choice?->value;
                }),
            ChoiceField::new('role')
                ->setChoices([
                    'Клиент' => MessageRole::CLIENT,
                    'Оператор' => MessageRole::OPERATOR,
                ])
                ->allowMultipleChoices(false)
                ->renderExpanded(false)
                ->renderAsBadges()
                ->setFormTypeOption('choice_value', function (?MessageRole $choice) {
                    return $choice?->value;
                })->setDisabled(),
            DateTimeField::new('createdAt')->setColumns('col-md-10')->setDisabled(),
            // TextEditorField::new('description'),
        ];
    }
}
