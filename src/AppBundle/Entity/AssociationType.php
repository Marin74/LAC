<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AssociationType
 *
 * @ORM\Table(name="association_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AssociationTypeRepository")
 */
class AssociationType
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
     * @ORM\Column(name="color", type="string", length=255, unique=true)
     */
    private $color;
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Association", cascade={"persist"},mappedBy="types")
     * @ORM\JoinTable(name="association_association_type",
     *      joinColumns={@ORM\JoinColumn(name="type_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="association_id", referencedColumnName="id")}
     *      )
     **/
    private $associations;


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
     * @return AssociationType
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
    
    /**
     * Set color
     *
     * @param string $color
     *
     * @return AssociationType
     */
    public function setColor($color)
    {
        $this->color = $color;
        
        return $this;
    }
    
    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }
}

