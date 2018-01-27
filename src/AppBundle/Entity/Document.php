<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * File
 *
 * @ORM\Table(name="document")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FileRepository")
 */
class Document
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
     * @ORM\Column(name="number", type="integer")
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Association",inversedBy="documents")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $association;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event",inversedBy="documents")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $event;
    
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $file;

    public function __construct() {
    	$this->setNumber(-1);
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
     * @return File
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
    
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }
    
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return Document
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    public function setAssociation($association) {
        $this->association = $association;

        return $this;
    }

    public function getAssociation() {
        return $this->association;
    }
    
    public function setEvent($event) {
        $this->event = $event;
        
        return $this;
    }
    
    public function getEvent() {
        return $this->event;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    public function upload()
    {
        // If there is no file, we do nothing
        if (null === $this->file) {
            return false;
        }

        // Get the original filename
        $name = $this->file->getClientOriginalName();

        // Moves the document in the good directory
        $this->file->move($this->getUploadRootDir(), $name);
        
        $maxNumber = 0;
        // Get max number
        if($this->getAssociation() != null) {
            foreach($this->getAssociation()->getDocuments() as $document) {
            	if($document->getNumber() > $maxNumber)
            		$maxNumber = $document->getNumber();
            }
        }
        elseif($this->getEvent() != null) {
            foreach($this->getEvent()->getDocuments() as $document) {
                if($document->getNumber() > $maxNumber)
                    $maxNumber = $document->getNumber();
            }
        }
        
        $maxNumber++;

        // Rename
        $mime = mime_content_type($this->getUploadRootDir()."/".$name);
        $mimeExplode = explode("/", $mime);
        $filename = "";
        if($this->getAssociation() != null) {
            
            $filename = "association_".$this->getAssociation()->getId()."_".$maxNumber.".".$mimeExplode[1];
        }
        elseif($this->getEvent() != null) {
            
            $filename = "event_".$this->getEvent()->getId()."_".$maxNumber.".".$mimeExplode[1];
        }
        $newPath = $this->getUploadRootDir()."/".$filename;
        rename($this->getUploadRootDir()."/".$name, $newPath);

        // Saves the filename
        $this->setFilename($filename);
        $this->setNumber($maxNumber);
        
        return true;
    }

    public function getUploadDir()
    {
        // Returns the relative path for the twig code
        return 'upload/document';
    }

    protected function getUploadRootDir()
    {
        // Returns the relative page for the PHP code
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    public function getPath()
    {
        // Returns path + filename

        if(empty($this->getFilename()))
            return null;
        
        return $this->getUploadDir()."/".$this->getFilename();
    }
}

