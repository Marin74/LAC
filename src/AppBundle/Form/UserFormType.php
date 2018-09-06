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
                'placeholder'               => 'select_association',
                'attr'                      => ['class' => 'form-control',],
                'required'                  => false,
                'query_builder'             => function(EntityRepository $repository) {
                    
                    $qb = $repository->createQueryBuilder('a');
                    $qb->select("a")->where($qb->expr()->eq("a.isWorkshop", ":isWorkshop"));
                    $qb->setParameter("isWorkshop", false);
                    return $qb->orderBy('a.name', 'ASC');
                },
            ))
            ->add('workshop',       EntityType::class, array(
                'class'                     => 'AppBundle:Association',
                'choice_label'              => 'name',
                'multiple'                  => false,
                'placeholder'               => 'select_workshop',
                'attr'                      => ['class' => 'form-control',],
                'required'                  => false,
                'query_builder'             => function(EntityRepository $repository) {
                    
                    $qb = $repository->createQueryBuilder('a');
                    $qb->select("a")->where($qb->expr()->eq("a.isWorkshop", ":isWorkshop"));
                    $qb->setParameter("isWorkshop", true);
                    return $qb->orderBy('a.name', 'ASC');
                },
            ))
            ->add('roles',			ChoiceType::class, array(
            		'choices' => array(
            				User::ROLE_DEFAULT => User::ROLE_DEFAULT,
                		    User::ROLE_ADMIN => User::ROLE_ADMIN,
                		    User::ROLE_ADMIN_WORKSHOP => User::ROLE_ADMIN_WORKSHOP
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