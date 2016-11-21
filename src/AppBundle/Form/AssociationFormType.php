<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class AssociationFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',               TextType::class)
            ->add('description',        TextareaType::class)
            ->add('website',            UrlType::class, array(
            		'required'	=> false
            	)
            )
            ->add('email',              TextType::class, array(
            		'required'	=> false
            	)
            )
            ->add('phone',              TextType::class, array(
            		'required'	=> false
            	)
            )
            ->add('street',             TextType::class, array(
            		'required'	=> false
            	)
            )
            ->add('zipCode',            TextType::class, array(
            		'required'	=> false
            	)
            )
            ->add('city',               TextType::class, array(
            		'required'	=> false
            	)
            )
            ->add('urlVideo',           TextType::class, array(
            		'required'	=> false
            	)
            )
            ->add('urlFacebook',        UrlType::class, array(
            		'required'	=> false
            	)
            )
            ->add('file',               FileType::class, array(
            		'required'	=> false
            	)
            )
            ->add('displayed',			CheckboxType::class)
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Association'
        ));
    }
}
