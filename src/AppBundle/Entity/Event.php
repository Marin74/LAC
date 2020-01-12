<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 */
class Event
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
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=50,
     *     max="2500"
     * )
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startTime", type="datetime")
     * @Assert\NotBlank()
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endTime", type="datetime")
     * @Assert\NotBlank()
     */
    private $endTime;

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=255, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=255, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Association", inversedBy="events")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $association;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Place", inversedBy="events")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $placeEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;
    
    /**
     * @var string
     *
     * @ORM\Column(name="websiteTitle", type="string", length=255, nullable=true)
     */
    private $websiteTitle;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;
    
    /**
     * @var string
     *
     * @ORM\Column(name="carpool", type="string", length=255, nullable=true)
     */
    private $carpool;

    /**
     * @var boolean
     *
     * @ORM\Column(name="searchVolunteers", type="boolean", nullable=false)
     * @Assert\NotNull()
     */
    private $searchVolunteers = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="published", type="boolean", nullable=false)
     * @Assert\NotNull()
     */
    private $published = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="free", type="boolean", nullable=false)
     * @Assert\NotNull()
     */
    private $free = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="pricing", type="string", length=255, nullable=true)
     */
    private $pricing;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="string", length=1, nullable=false)
     */
    private $status;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime", nullable=true)
     */
    private $creationDate;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updateDate", type="datetime", nullable=true)
     */
    private $updateDate;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Document",mappedBy="event")
     * @ORM\OrderBy({"name": "asc"})
     */
    private $documents;

    private $file;
    
    const STATUS_CANCELED = "C";
    const STATUS_POSTPONED = "P";
    const STATUS_SCHEDULED = "S";
    
    
    public function __construct() {
        
        $this->setCreationDate(new \DateTime());
        $this->setStatus(Event::STATUS_SCHEDULED);
    }


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
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
        
        $this->setUpdateDate(new \DateTime());

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
        $name = "action";

        if (!empty($this->getName())) {
            $name = strtolower(trim($this->getName()));
            
            $name = str_replace("’", "-", $name);
            $name = str_replace("'", "-", $name);
            $name = str_replace('"', "", $name);
            $name = str_replace("/", "", $name);
            $name = str_replace("|", "", $name);
            $name = str_replace('\\', "", $name);
            $name = str_replace('?', "", $name);
            $name = str_replace('!', "", $name);
            $name = str_replace('(', "", $name);
            $name = str_replace(')', "", $name);
            $name = str_replace('{', "", $name);
            $name = str_replace('}', "", $name);
            $name = str_replace('[', "", $name);
            $name = str_replace(']', "", $name);
            $name = str_replace('°', "", $name);
            $name = str_replace('&', "", $name);
            $name = str_replace('§', "", $name);
            $name = str_replace('*', "", $name);
            $name = str_replace('%', "", $name);
            $name = str_replace(':', "", $name);
            $name = str_replace(',', "", $name);
            $name = str_replace(';', "", $name);
            $name = str_replace('=', "", $name);
            $name = str_replace('+', "", $name);
            $name = str_replace('.', "", $name);
            $name = str_replace('<', "", $name);
            $name = str_replace('>', "", $name);
            $name = str_replace('@', "", $name);
            $name = str_replace('#', "", $name);
            
            $name = trim($name);
            $name = str_replace(" ", "-", $name);
            
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
            $name = str_replace("û", "u", $name);
            $name = str_replace("î", "i", $name);
            $name = str_replace("ï", "i", $name);
            $name = str_replace("ç", "c", $name);
            $name = str_replace("æ", "ae", $name);
            $name = str_replace("œ", "oe", $name);
            
            // Uppercase
            $name = str_replace("É", "e", $name);
            $name = str_replace("È", "e", $name);
            $name = str_replace("Ê", "e", $name);
            $name = str_replace("Ë", "e", $name);
            $name = str_replace("Â", "a", $name);
            $name = str_replace("À", "a", $name);
            $name = str_replace("Ä", "a", $name);
            $name = str_replace("Ô", "o", $name);
            $name = str_replace("Ö", "o", $name);
            $name = str_replace("Ù", "u", $name);
            $name = str_replace("Ü", "u", $name);
            $name = str_replace("Î", "i", $name);
            $name = str_replace("Ï", "i", $name);
            $name = str_replace("Ç", "c", $name);
            $name = str_replace("Æ", "ae", $name);
            $name = str_replace("Œ", "oe", $name);
            
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
     * @return Event
     */
    public function setDescription($description)
    {
        $this->description = $description;
        
        $this->setUpdateDate(new \DateTime());

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
     * Set startTime
     *
     * @param \DateTime $startTime
     *
     * @return Event
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
        
        $this->setUpdateDate(new \DateTime());

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param \DateTime $endTime
     *
     * @return Event
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
        
        $this->setUpdateDate(new \DateTime());

        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set picture
     *
     * @param string $picture
     *
     * @return Event
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
        
        $this->setUpdateDate(new \DateTime());

        return $this;
    }

    /**
     * Get picture
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        
        $this->setUpdateDate(new \DateTime());

        return $this;
    }
    
    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        
        $this->setUpdateDate(new \DateTime());

        return $this;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setAssociation($association)
    {
        $this->association = $association;
        
        $this->setUpdateDate(new \DateTime());

        return $this;
    }

    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return Event
     */
    public function setWebsite($website)
    {
        $this->website = $website;
        
        $this->setUpdateDate(new \DateTime());

        return $this;
    }
    
    /**
     * Set websiteTitle
     *
     * @param string $websiteTitle
     *
     * @return Event
     */
    public function setWebsiteTitle($websiteTitle)
    {
        $this->websiteTitle = $websiteTitle;
        
        $this->setUpdateDate(new \DateTime());
        
        return $this;
    }
    
    /**
     * Set email
     *
     * @param string $email
     *
     * @return Event
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
     * Set carpool
     *
     * @param string $carpool
     *
     * @return Event
     */
    public function setCarpool($carpool)
    {
        $this->carpool = $carpool;
        
        $this->setUpdateDate(new \DateTime());
        
        return $this;
    }

    public function setSearchVolunteers($searchVolunteers)
    {
        $this->searchVolunteers = $searchVolunteers;
        
        $this->setUpdateDate(new \DateTime());

        return $this;
    }

    public function getSearchVolunteers()
    {
        return $this->searchVolunteers;
    }

    public function setPublished($published)
    {
        $this->published = $published;
        
        $this->setUpdateDate(new \DateTime());

        return $this;
    }

    public function isPublished()
    {
        return $this->published;
    }

    public function setFree($free)
    {
        $this->free = $free;
        
        $this->setUpdateDate(new \DateTime());

        return $this;
    }

    public function getFree()
    {
        return $this->free;
    }
    
    public function setCanceled($canceled)
    {
        $this->canceled = $canceled;
        
        $this->setUpdateDate(new \DateTime());
        
        return $this;
    }

    public function setPricing($pricing)
    {
        $this->pricing = $pricing;
        
        $this->setUpdateDate(new \DateTime());

        return $this;
    }

    public function getPricing()
    {
        return $this->pricing;
    }
    
    public function setStatus($status)
    {
        $this->status = $status;
        
        $this->setUpdateDate(new \DateTime());
        
        return $this;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function isCanceled()
    {
        return $this->getStatus() == Event::STATUS_CANCELED;
    }
    
    public function isPostponed()
    {
        return $this->getStatus() == Event::STATUS_POSTPONED;
    }

    public function getQuizzScores()
    {
        return $this->quizzScores;
    }
    
    public function setPlaceEntity($place)
    {
        $this->placeEntity = $place;
        
        $this->setUpdateDate(new \DateTime());
        
        return $this;
    }
    
    public function getPlaceEntity()
    {
        return $this->placeEntity;
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
     * Get websiteTitle
     *
     * @return string
     */
    public function getWebsiteTitle()
    {
        return $this->websiteTitle;
    }
    
    /**
     * Get carpool
     *
     * @return string
     */
    public function getCarpool()
    {
        return $this->carpool;
    }
    
    public function getDocuments()
    {
        return $this->documents;
    }
    
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        
        return $this;
    }
    
    public function getCreationDate()
    {
        return $this->creationDate;
    }
    
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
        
        return $this;
    }
    
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    public function takesLessThanOneDay() {
        $oneDayLater = clone $this->getStartTime();
        $oneDayLater->add(new \DateInterval("PT24H"));
        
        return $this->getEndTime() < $oneDayLater && ($this->getStartTime()->format("Y-m-d") == $this->getEndTime()->format("Y-m-d") || intval($this->getEndTime()->format("H")) <= 5);
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
        $filename = "event_".$this->getId().".".$mimeExplode[1];
        $newPath = $this->getUploadRootDir()."/".$filename;
        rename($this->getUploadRootDir()."/".$name, $newPath);

        // Saves the filename
        $this->setPicture($filename);
        
        $this->setUpdateDate(new \DateTime());

        return true;
    }

    public function getUploadDir()
    {
        // Returns the relative path for the twig code
        return 'upload/img/event';
    }

    protected function getUploadRootDir()
    {
        // Returns the relative page for the PHP code
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    public function getPicturePath()
    {
        // Returns path + filename
        
        if(empty($this->getPicture()))
            return null;
        
        return $this->getUploadDir()."/".$this->getPicture();
    }
}

