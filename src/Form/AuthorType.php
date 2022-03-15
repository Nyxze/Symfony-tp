<?php

namespace App\Form;

use App\DataFixtures\AuthorFixtures;
use App\Entity\Author;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $nationalities = [];
        foreach (AuthorFixtures::$nationalities as $item){
            $nationalities[$item] = $item;
        }

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('nationality', ChoiceType::class, [
                'label' => 'Nationalité',
                'choices' => $nationalities,
                //'expanded' => true,
                //'multiple' => true
            ])
            ->add('plainPassword')
            ->add('email');
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Author::class,
        ]);
    }
}
