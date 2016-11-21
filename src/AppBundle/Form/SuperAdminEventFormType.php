<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

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
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('association',      EntityType::class, array(
                'class'                     => 'AppBundle:Association',
                'choice_label'              => 'name',
                'multiple'                  => false,
                'placeholder'               => 'select_association',
                'attr'                      => ['class' => 'form-control',],
                'query_builder'             => function(EntityRepository $repository) {
                    return $repository->createQueryBuilder('u')->orderBy('u.name', 'ASC');
                },
            ))
            ->add('startTime',      DateTimeType::class, array(
           		'widget' => 'single_text',
            	'view_timezone' => $timeZoneDisplayed,
            	'format' => 'dd-MM-yyyy HH:mm', 'attr' => [
                	'class' => 'form-control input-inline form_datetime',
            ]))
            ->add('endTime',        DateTimeType::class, array(
            	'widget' => 'single_text',
            	'view_timezone' => $timeZoneDisplayed,
           		'format' => 'dd-MM-yyyy HH:mm',
           		'attr' => [
               		'class' => 'form-control input-inline form_datetime',
            ]))
            ->add('description',    TextareaType::class, array(
                'attr'                      => ['class' => 'form-control', 'rows' => 10],
            ))
            ->add('website',        UrlType::class, array(
                'attr'                      => ['class' => 'form-control',],
            	'required'					=> false
            ))
            ->add('place',          TextType::class, array(
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('street',         TextType::class, array(
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('zipCode',        TextType::class, array(
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('city',           TextType::class, array(
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('latitude',       NumberType::class, array(
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('longitude',      NumberType::class, array(
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('file',           FileType::class, array(
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('free',				CheckboxType::class, array(
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('pricing',			TextType::class, array(
                'attr'                      => ['class' => 'form-control',],
            	'required'					=> false
            ))
            ->add('searchVolunteers',	CheckboxType::class, array(
                'attr'                      => ['class' => 'form-control',],
            ))
            ->add('published',			CheckboxType::class, array(
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
