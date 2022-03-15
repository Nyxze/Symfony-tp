<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Tag;
use App\Form\DataTransformer\TagDataTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    private TagDataTransformer $tagTransformer;

    /**
     * @param TagDataTransformer $tagTransformer
     */
    public function __construct(TagDataTransformer $tagTransformer)
    {
        $this->tagTransformer = $tagTransformer;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('content', TextareaType::class, [
                'label' => 'Texte',
                'attr' => ['rows' => 10]
            ])
            ->add('category', EntityType::class,[
                'class' => Category::class,
                'choice_label' => 'categoryName',
                'label' => 'Catégorie'
            ])
            ->add('uploadedFile', FileType::class, [
                'label' => 'La photo de l\'article',
                'required' => false,
                //'mapped' => false
            ])
            ->add('tags', TextType::class, [
                'label' => 'Tags'
            ])
        ;

        // Définition du transformer sur la propriété tags
        $builder->get('tags')->addModelTransformer($this->tagTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
