<?php

namespace App\Form\Crud;

use App\Entity\Message;
use App\Enum\MessageStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextType::class, [
                'disabled' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Сообщение',
                ],
            ])
            ->add('status', EnumType::class, [
                'disabled' => true,
                'class' => MessageStatus::class,
                'choice_label' => function (MessageStatus $status) {
                    return $status->value;
                },
                'attr' => ['class' => 'form-select'],
            ])
            ->add('createdAt', DateTimeType::class, [
                'disabled' => true,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control datetimepicker',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
