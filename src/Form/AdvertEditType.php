<?php

// cette classe hérite de AdvertType et a pour but d'être réutilisé partout si on le veut. fini le copier-coller

namespace App\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class AdvertEditType extends AbstractType
{
    /**
     * suite a son héritage, AdvertEditType peut modifier le formulaire, par exemple ,
     * on va supprimer le champ date du formulaire de AdvertEditType , pour ne pas le modifié
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('date');
    }

    /**
     * grace à getParent(), AdvertEditType va hérité de la composition du formulaire de AdvertType 
     * pour évité de réécrire le même formulaire
     */
    public function getParent()
    {
        return AdvertType::class;
    }
}    