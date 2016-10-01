<?php
// src/AppBundle/Form/RegistrationType.php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\User;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('association',      EntityType::class, array(
                'class'                     => 'AppBundle:Association',
                'choice_label'              => 'name',
                'multiple'                  => false,
                'choice_translation_domain' => 'messages',
                'placeholder'               => 'select_association',
                'attr'                      => ['class' => 'form-control',],
                'query_builder'             => function(EntityRepository $repository) {
                    return $repository->createQueryBuilder('u')->orderBy('u.name', 'ASC');
                },
            ))
            ->add('roles',			ChoiceType::class, array(
            		'choices' => array(
            				User::ROLE_DEFAULT => User::ROLE_DEFAULT,
            				User::ROLE_ADMIN => User::ROLE_ADMIN
            		),
	                'multiple'                  => true,
	                'choice_translation_domain' => 'messages'
            ));
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }
}