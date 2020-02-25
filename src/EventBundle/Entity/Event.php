<?php

namespace EventBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * event
 * @Vich\Uploadable
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="EventBundle\Repository\EventRepository")
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
     * @Assert\NotNull(message="Le libelle doit étre fournis")
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var string
     * @Assert\NotNull(message="La description doit étre fournis")
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var string
     * @Assert\NotNull(message="Vous devez fournir le type d'evenement")
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var \DateTime
     * @Assert\NotNull(message="Vous devez fournir la date")
     * @Assert\GreaterThanOrEqual("today", message="La date de l'évenement doit etre supérieur à d'aujourd'hui ")
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var int
     * @Assert\NotNull(message="Vous devez fournir le nombre de places")
     * @ORM\Column(name="nbrPlaces", type="integer")
     */
    private $nbrPlaces;

    /**
     * @var int
     * @Assert\NotNull()
     * @ORM\Column(name="nbrParticipants", type="integer")
     */
    private $nbrParticipants;

    /**
     * @var string
     * @Assert\NotNull(message="Vous devez fournir le lieu")
     * @ORM\Column(name="lieu", type="string", length=255)
     */
    private $lieu;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="createdBy",referencedColumnName="id",onDelete="CASCADE")
     */
    private $createdBy;


    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinTable(name="event_user",
     *   joinColumns={
     *     @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   }
     * )
     */
    private $participants;

    /**
     * @Vich\UploadableField(mapping="evt_cover", fileNameProperty="cover")
     *
     * @var File
     */
    private $evtCover;

    /**
     * @ORM\Column(name="cover", type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $cover;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     *
     * @var \DateTime
     */
    private $coverUpdatedAt;

    /**
     * Event constructor.
     * @param int $nbrParticipants
     * @param \Doctrine\Common\Collections\Collection $participants
     */
    public function __construct()
    {
        $this->nbrParticipants = 0;
        $this->participants = new ArrayCollection();
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
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getNbrPlaces()
    {
        return $this->nbrPlaces;
    }

    /**
     * @param int $nbrPlaces
     */
    public function setNbrPlaces($nbrPlaces)
    {
        $this->nbrPlaces = $nbrPlaces;
    }

    /**
     * @return int
     */
    public function getNbrParticipants()
    {
        return $this->nbrParticipants;
    }

    /**
     * @param int $nbrParticipants
     */
    public function setNbrParticipants($nbrParticipants)
    {
        $this->nbrParticipants = $nbrParticipants;
    }

    /**
     * @return string
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * @param string $lieu
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $responsable
     */
    public function setCreatedBy($responsable)
    {
        $this->createdBy = $responsable;
    }


    /**
     * @return File
     */
    public function getEvtCover()
    {
        return $this->evtCover;
    }

    /**
     * @param File $evtCover
     */
    public function setEvtCover($evtCover)
    {
        $this->evtCover = $evtCover;
    }

    /**
     * @return string
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * @param string $cover
     */
    public function setCover($cover)
    {
        $this->cover = $cover;
    }

    /**
     * @return \DateTime
     */
    public function getCoverUpdatedAt()
    {
        return $this->coverUpdatedAt;
    }

    /**
     * @param \DateTime $coverUpdatedAt
     */
    public function setCoverUpdatedAt($coverUpdatedAt)
    {
        $this->coverUpdatedAt = $coverUpdatedAt;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $participants
     */
    public function setParticipants($participants)
    {
        $this->participants = $participants;
    }

    public function addParticipant(User $user)
    {
        $this->participants->add($user);
    }

    public function removeParticipant(User $user)
    {
        $this->participants->removeElement($user);
    }
}

