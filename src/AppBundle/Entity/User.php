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
}

