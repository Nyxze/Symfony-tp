<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Tag;
use App\Form\DataTransformer\TagDataTransformer;
use App\Repository\AuthorRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ArticleType extends AbstractType
{
    private TagDataTransformer $tagTransformer;
    private Security $security;

    /**
     * @param TagDataTransformer $tagTransformer
     */
    public function __construct(TagDataTransformer $tagTransformer, Security $security)
    {
        $this->tagTransformer = $tagTransformer;
        $this->security = $security;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre','required'=>false])
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
            ->add('coAuthor',EntityType::class,
            [
                'class'=>Author::class,
                'choice_label'=>'fullName',
                'query_builder'=>function(AuthorRepository $repository){

                return $repository->getPotentialCoAuthor($this->security->getUser());
                }
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
