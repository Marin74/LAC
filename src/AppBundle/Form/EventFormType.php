<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventFormType extends AbstractType
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
                'required' => true
            ))
            ->add('startTime',      DateTimeType::class, array('required' => true, 'widget' => 'single_text', 'view_timezone' => $timeZoneDisplayed, 'format' => 'dd-MM-yyyy HH:mm', 'attr' => [
                'class' => 'form-control input-inline form_datetime',
            ]))
            ->add('endTime',        DateTimeType::class, array('required' => true, 'widget' => 'single_text', 'view_timezone' => $timeZoneDisplayed, 'format' => 'dd-MM-yyyy HH:mm', 'attr' => [
                'class' => 'form-control input-inline form_datetime',
            ]))
            ->add('description',    TextareaType::class, array(
                'required' => true
            ))
            ->add('place',          TextType::class, array(
                'required' => false,
            ))
            ->add('street',         TextType::class, array(
                'required' => false,
            ))
            ->add('zipCode',        TextType::class, array(
                'required' => false,
            ))
            ->add('city',           TextType::class, array(
                'required' => false,
            ))
            ->add('latitude',       NumberType::class, array(
                'required' => true,
            ))
            ->add('longitude',      NumberType::class, array(
                'required' => true,
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
