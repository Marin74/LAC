<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuizCategory
 *
 * @ORM\Table(name="quiz_category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuizCategoryRepository")
 */
class QuizCategory
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="order", type="integer", unique=true)
     */
    private $order;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\QuizScore",mappedBy="quizCategory")
     */
    private $quizScores;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return QuizCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function setOrder($order)
    {
    	$this->order = $order;
    	
    	return $this;
    }
    
    public function getOrder()
    {
    	return $this->order;
    }

    public function getQuizScores()
    {
        return $this->quizScores;
    }
}

