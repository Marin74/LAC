<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SuperAdminAssociationFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('types',              EntityType::class, array(
                'class'                     => 'AppBundle:AssociationType',
                'choice_label'              => 'name',
                'multiple'                  => true,
                'choice_translation_domain' => 'messages',
                'required'					=> false
            ))
        ;
    }
    
    public function getParent()
    {
        return 'AppBundle\Form\AssociationFormType';
    }
}
