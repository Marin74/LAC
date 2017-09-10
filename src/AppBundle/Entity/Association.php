<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Association
 *
 * @ORM\Table(name="association")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AssociationRepository")
 */
class Association
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
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=100,
     *     max="2000"
     * )
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(name="zipCode", type="string", length=255, nullable=true)
     */
    private $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="urlVideo", type="string", length=255, nullable=true)
     */
    private $urlVideo;

    /**
     * @var string
     *
     * @ORM\Column(name="urlFacebook", type="string", length=255, nullable=true)
     */
    private $urlFacebook;

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @var boolean
     *
     * @ORM\Column(name="displayed", type="boolean", nullable=false)
     * @Assert\NotNull()
     */
    private $displayed = true;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Event",mappedBy="association")
     * @OrderBy({"startTime" = "ASC"})
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User",mappedBy="association")
     */
    private $owners;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Document",mappedBy="association")
     * @ORM\OrderBy({"name": "asc"})
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\QuizScore",mappedBy="association")
     * @ORM\OrderBy({"score": "desc"})
     */
    private $quizScores;

    private $file;


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
     * @return Association
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

    public function getNameForUrl()
    {
        $name = "acteur";

        if (!empty($this->getName())) {
            $name = $this->getName();

            $name = str_replace(" ", "-", $name);
            $name = str_replace("'", "", $name);
            $name = str_replace('"', "", $name);
            $name = str_replace("/", "", $name);
            $name = str_replace("|", "", $name);
            $name = str_replace('\\', "", $name);
            $name = str_replace('?', "", $name);
            $name = str_replace('!', "", $name);
            $name = str_replace('(', "", $name);
            $name = str_replace(')', "", $name);
            $name = str_replace('°', "", $name);
            $name = str_replace('&', "", $name);
            $name = str_replace('§', "", $name);
            $name = str_replace('*', "", $name);
            $name = str_replace('%', "", $name);
            $name = str_replace(':', "", $name);
            $name = str_replace(',', "", $name);
            $name = str_replace('=', "", $name);
            $name = str_replace('+', "", $name);
            $name = str_replace('%', "", $name);
            $name = str_replace('.', "", $name);
            $name = str_replace('<', "", $name);
            $name = str_replace('>', "", $name);
            $name = str_replace('@', "", $name);
            $name = str_replace('#', "", $name);
            
            // Lowercase
            $name = str_replace("é", "e", $name);
            $name = str_replace("è", "e", $name);
            $name = str_replace("ê", "e", $name);
            $name = str_replace("ë", "e", $name);
            $name = str_replace("â", "a", $name);
            $name = str_replace("à", "a", $name);
            $name = str_replace("ä", "a", $name);
            $name = str_replace("ô", "o", $name);
            $name = str_replace("ö", "o", $name);
            $name = str_replace("ù", "u", $name);
            $name = str_replace("ü", "u", $name);
            $name = str_replace("ù", "u", $name);
            $name = str_replace("î", "i", $name);
            $name = str_replace("ï", "i", $name);
            $name = str_replace("ç", "c", $name);
            $name = str_replace("æ", "ae", $name);
            $name = str_replace("œ", "oe", $name);

            // Uppercase
            $name = str_replace("É", "E", $name);
            $name = str_replace("È", "E", $name);
            $name = str_replace("Ê", "E", $name);
            $name = str_replace("Ë", "E", $name);
            $name = str_replace("Â", "A", $name);
            $name = str_replace("À", "A", $name);
            $name = str_replace("Ä", "A", $name);
            $name = str_replace("Ô", "O", $name);
            $name = str_replace("Ö", "O", $name);
            $name = str_replace("Ù", "U", $name);
            $name = str_replace("Ü", "U", $name);
            $name = str_replace("Î", "I", $name);
            $name = str_replace("Ï", "I", $name);
            $name = str_replace("Ç", "C", $name);
            $name = str_replace("Æ", "AE", $name);
            $name = str_replace("Œ", "OE", $name);
            
            while(strpos($name, "--") !== false) {
            	
            	$name = str_replace("--", "-", $name);
            }
        }

        return $name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Association
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return Association
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Association
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Association
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Association
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return Association
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Association
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    public function setUrlVideo($urlVideo)
    {
        $this->urlVideo = $urlVideo;

        return $this;
    }

    public function getUrlVideo()
    {
        return $this->urlVideo;
    }

    public function getUrlFacebook()
    {
        return $this->urlFacebook;
    }
    
    public function setUrlFacebook($urlFacebook)
    {
        $this->urlFacebook = $urlFacebook;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function getNextEvents() {

        $nextEvents = array();
        $now = new \DateTime();

        foreach($this->getEvents() as $event) {
            if($now < $event->getEndTime() && $event->isPublished())
                $nextEvents[] = $event;
        }

        return $nextEvents;
    }
    
    public function getFullAddress()
    {
        $address = "";

        if (!empty($this->getStreet())) {
            $address = $this->getStreet();

            if (!empty($this->getZipCode()))
                $address .= ", " . $this->getZipCode();
        }

        if (!empty($this->getCity())) {
            
            if(!empty($address)) {
                if(!empty($this->getZipCode()))
                    $address .= " ";
                else
                    $address .= ", ";
            }
            
            $address .= $this->getCity();
        }
        
        return $address;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function setPicture($picture)
    {
        $this->picture = $picture;
    }
    
    public function isDisplayed()
    {
    	return $this->displayed;
    }
    
    public function setDisplayed($displayed)
    {
    	$this->displayed = $displayed;
    }

    public function getOwners()
    {
        return $this->owners;
    }

    public function getDocuments()
    {
        return $this->documents;
    }

    public function getQuizScores()
    {
        return $this->quizScores;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    public function uploadPicture()
    {
        // If there is no file, we do nothing
        if (null === $this->file) {
            return false;
        }

        // Get the original filename
        $name = $this->file->getClientOriginalName();

        // Moves the picture in the good directory
        $this->file->move($this->getUploadRootDir(), $name);

        // Rename
        $mime = mime_content_type($this->getUploadRootDir()."/".$name);
        $mimeExplode = explode("/", $mime);
        $filename = "association_".$this->getId().".".$mimeExplode[1];
        $newPath = $this->getUploadRootDir()."/".$filename;
        rename($this->getUploadRootDir()."/".$name, $newPath);

        // Saves the filename
        $this->setPicture($filename);
        
        return true;
    }

    public function getUploadDir()
    {
        // Returns the relative path for the twig code
        return 'upload/img/association';
    }

    protected function getUploadRootDir()
    {
        // Returns the relative page for the PHP code
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    public function getUploadVideoDir()
    {
        // Returns the relative path for the twig code
        return 'upload/video';
    }

    public function getPicturePath()
    {
        // Returns path + filename

        if(empty($this->getPicture()))
            return null;
        
        return $this->getUploadDir()."/".$this->getPicture();
    }

    public function getUrlVideoPath()
    {
        // Returns path + filename
        
    	if(empty($this->getUrlVideo()))
    		return null;
    	
    	return $this->getUploadVideoDir()."/".$this->getUrlVideo();
    }
}

