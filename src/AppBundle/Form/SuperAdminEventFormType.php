<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuperAdminEventFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $timeZoneDisplayed = "Europe/Paris";
        
        $builder
            ->add('name',           TextType::class, array(
                'required'                  => true,
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('association',      EntityType::class, array(
                'class'                     => 'AppBundle:Association',
                'choice_label'              => 'name',
                'multiple'                  => false,
                'required'                  => true,
                'choice_translation_domain' => 'messages',
                'placeholder'               => 'select_association',
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('startTime',      DateTimeType::class, array('required' => true, 'widget' => 'single_text', 'view_timezone' => $timeZoneDisplayed, 'format' => 'dd-MM-yyyy HH:mm', 'attr' => [
                'class' => 'form-control input-inline form_datetime',
            ]))
            ->add('endTime',        DateTimeType::class, array('required' => true, 'widget' => 'single_text', 'view_timezone' => $timeZoneDisplayed, 'format' => 'dd-MM-yyyy HH:mm', 'attr' => [
                'class' => 'form-control input-inline form_datetime',
            ]))
            ->add('description',    TextareaType::class, array(
                'required'                  => true,
                'attr'                      => ['class' => 'form-control', 'rows' => 10],
            ))
            ->add('place',          TextType::class, array(
                'required'                  => false,
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('street',         TextType::class, array(
                'required'                  => false,
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('zipCode',        TextType::class, array(
                'required'                  => false,
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('city',           TextType::class, array(
                'required'                  => false,
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('latitude',       NumberType::class, array(
                'required'                  => true,
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('longitude',      NumberType::class, array(
                'required'                  => true,
                'attr'                      => ['class' => 'form-control',],
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Event'
        ));
    }
}
