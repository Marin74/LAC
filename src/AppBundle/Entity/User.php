<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    const ROLE_ADMIN = 'ROLE_ADMIN';// ROLE_USER and ROLE_SUPER_ADMIN already exist in the UserInterface class
    const ROLE_ADMIN_WORKSHOP = 'ROLE_ADMIN_WORKSHOP';
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Association",inversedBy="owners")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $association;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Association",inversedBy="workshopOwners")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $workshop;

    public function __construct()
    {
        parent::__construct();
    }

    public function setAssociation($association) {
        $this->association = $association;

        return $this;
    }

    public function getAssociation() {
        return $this->association;
    }
    
    public function setWorkshop($workshop) {
        $this->workshop = $workshop;
        
        return $this;
    }
    
    public function getWorkshop() {
        return $this->workshop;
    }
    
    public function isAdmin() {
        return in_array(User::ROLE_ADMIN, $this->getRoles());
    }
}

