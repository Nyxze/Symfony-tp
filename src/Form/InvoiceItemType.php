<?php

namespace App\Form;

use App\Entity\InvoiceItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label')
            ->add('unitPrice')
            ->add('qt')
            ->add('deleteButton',ButtonType::class,
            [
                'label'=>'X',
                'attr'=>['class'=>'btn btn-danger delete',
                    'style'=>'margin-top:32px']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InvoiceItem::class,
            'attr'=>['class'=>'d-flex']
        ]);
    }
}
