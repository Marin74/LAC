<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * QuizScore
 *
 * @ORM\Table(name="quiz_score")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuizScoreRepository")
 */
class QuizScore
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
     * @var int
     *
     * @ORM\Column(name="score", type="integer")
     * @Assert\NotNull()
     */
    private $score;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\QuizCategory", inversedBy="quizScores")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $quizCategory;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Association", inversedBy="quizScores")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $association;


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
     * Set score
     *
     * @param integer $score
     *
     * @return QuizScore
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }

    public function setQuizCategory($quizCategory)
    {
    	$this->quizCategory = $quizCategory;
    
    	return $this;
    }
    
    public function getQuizCategory()
    {
    	return $this->quizCategory;
    }

    public function setAssociation($association)
    {
    	$this->association = $association;
    
    	return $this;
    }
    
    public function getAssociation()
    {
    	return $this->association;
    }
}

