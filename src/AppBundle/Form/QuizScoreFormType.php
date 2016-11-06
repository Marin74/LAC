<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class QuizScoreFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        	->add('association',	EntityType::class, array(
				'class'                     => 'AppBundle:Association',
				'choice_label'              => 'name',
				'multiple'                  => false,
				'placeholder'               => 'select_association',
			))
        	->add('quizCategory',	EntityType::class, array(
				'class'                     => 'AppBundle:QuizCategory',
				'choice_label'              => 'name',
				'multiple'                  => false,
				'choice_translation_domain' => 'messages',
				'placeholder'               => 'select_quiz_category',
			))
			->add('score',			ChoiceType::class, array(
				'choices' => array(
					'score_exactly'			=> 2,
					'score_moderately'		=> 1,
					'score_vaguely'			=> 0
				),
				'placeholder'              => 'select_score',
			))
		;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\QuizScore'
        ));
    }
}
