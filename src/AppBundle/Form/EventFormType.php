<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use AppBundle\Entity\Event;

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
            ->add('name',				TextType::class)
            ->add('startTime',			DateTimeType::class, array(
           		'widget' => 'single_text',
           		'view_timezone' => $timeZoneDisplayed,
            	'format' => 'dd-MM-yyyy HH:mm',
            	'attr' => [
                	'class' => 'form-control input-inline form_datetime',
            ]))
            ->add('endTime',			DateTimeType::class, array(
            	'widget' => 'single_text',
            	'view_timezone' => $timeZoneDisplayed,
           		'format' => 'dd-MM-yyyy HH:mm',
           		'attr' => [
               		'class' => 'form-control input-inline form_datetime',
            ]))
            ->add('description',		TextareaType::class)
            ->add('website',			UrlType::class, array(
            	'required'	=> false
        	)
            )
            ->add('websiteTitle',		TextType::class, array(
                'required'	=> false
            )
            )
            ->add('email',              EmailType::class, array(
                'required'	=> false
            )
            )
            ->add('carpool',			UrlType::class, array(
                'required'	=> false
            )
            )
            ->add('file',				FileType::class, array(
                'required'  => false
            )
            )
            ->add('free',				CheckboxType::class, array(
                'required'  => false
            )
            )
            ->add('pricing',			TextType::class, array(
            	'required' => false
            	)
            )
            ->add('searchVolunteers',	CheckboxType::class, array(
                'required'  => false
            )
            )
            ->add('status',             ChoiceType::class, array(
                'choice_translation_domain' => 'messages',
                'required'					=> true,
                'expanded'                  => true,
                'choices'                   => [
                    "scheduled"  => Event::STATUS_SCHEDULED,
                    "postponed"  => Event::STATUS_POSTPONED,
                    "canceled"   => Event::STATUS_CANCELED
                ]
            )
            )
            ->add('published',			CheckboxType::class, array(
                'required'  => false
            )
            )
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
