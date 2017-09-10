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
     * @ORM\Column(name="place", type="string", length=255, nullable=true)
     */
    private $place;

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
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $longitude;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Association", inversedBy="events")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $association;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

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
     * @return Event
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
        $name = "action";

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
     * @return Event
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
     * Set startTime
     *
     * @param \DateTime $startTime
     *
     * @return Event
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

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
     * Set place
     *
     * @param string $place
     *
     * @return Event
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return string
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Event
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
     * @return Event
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
     * @return Event
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
    	if(!empty($this->city))
    		return strtoupper($this->city);
    	
        return $this->city;
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

        return $this;
    }
    
    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLongitude()
    {
        return $this->longitude;
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

        return $this;
    }

    public function setSearchVolunteers($searchVolunteers)
    {
        $this->searchVolunteers = $searchVolunteers;

        return $this;
    }

    public function getSearchVolunteers()
    {
        return $this->searchVolunteers;
    }

    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    public function isPublished()
    {
        return $this->published;
    }

    public function setFree($free)
    {
        $this->free = $free;

        return $this;
    }

    public function getFree()
    {
        return $this->free;
    }

    public function setPricing($pricing)
    {
        $this->pricing = $pricing;

        return $this;
    }

    public function getPricing()
    {
        return $this->pricing;
    }

    public function getQuizzScores()
    {
        return $this->quizzScores;
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

    public function takesLessThanOneDay() {
        $oneDayLater = clone $this->getStartTime();
        $oneDayLater->add(new \DateInterval("PT24H"));
        
        return $this->getEndTime() < $oneDayLater;
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

