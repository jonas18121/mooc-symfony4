<?php

// cette classe a pour but d'être réutilisé partout si on le veut. fini le copier-coller

namespace App\Form;

use App\Entity\Advert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use App\Form\ImageType;
use App\Form\CategoryType;

use App\Entity\Category;
use App\Repository\CategoryRepository;

class AdvertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $pattern = 'D%';

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $builder
            ->add('title',      TextType::class)
            ->add('author',     TextType::class)
            ->add('content',    TextareaType::class)
            ->add('date',       DateType::class)
            ->add('published',  CheckboxType::class, [ 'required' => false ])
            ->add('image',      ImageType::class) // le formulaire de la classe Image est imbriqué dans celui de Advert

            /* 
            //imbriquer le formulaire de Category dans celui de Advert grace à CollectionType
            ->add('categories', CollectionType::class,[
                'entry_type'  =>CategoryType::class,
                'allow_add'   =>true,
                'allow_delete'=>true
            ])
            */


            //imbriquer le formulaire de Category dans celui de Advert grace à EntityType
            /* */
            ->add('categories', EntityType::class,[
                'class'  =>     Category::class,
                'choice_label'  => 'name',
                'multiple'      => false,

                /* pour afficher des category qui commence par une certaine lettre qui sera dans $pattern,
                ici on a choisi la lettre D 
                si on veut affiché toute la liste de catégorie sans ce préoccupé de leur première lettre
                on enlève l'option query_builder */
                'query_builder' => function(CategoryRepository $repository) use ($pattern)
                {
                    return $repository->getLikeQueryBuilder($pattern);
                }
            ])
            /**/


            ->add('save',       SubmitType::class)
        ;
    }

    /**
     * Ce formulaire se construira autour de l'objet Advert
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Advert::class,
        ]);
    }
}
