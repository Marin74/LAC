<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewsletterEvent
 *
 * @ORM\Table(name="newsletter_event")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsletterEventRepository")
 */
class NewsletterEvent
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Newsletter", inversedBy="newsletterEvents")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $newsletter;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $event;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
        
        return $this;
    }
    
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return NewsletterEvent
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
    
    public function setEvent($event)
    {
        $this->event = $event;
        
        return $this;
    }
    
    public function getEvent()
    {
        return $this->event;
    }
}

