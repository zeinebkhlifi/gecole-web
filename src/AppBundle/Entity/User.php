<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use EventBundle\Entity\Event;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="EventBundle\Entity\Event", mappedBy="participants")
     */
    private $eventsParticipes;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventsParticipes()
    {
        return $this->eventsParticipes;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $eventsParticipes
     */
    public function setEventsParticipes($eventsParticipes)
    {
        $this->eventsParticipes = $eventsParticipes;
    }

    public function addEventsParticipes(Event $event)
    {
        $this->eventsParticipes->add($event);
    }

    public function removeEventsParticipes(Event $event)
    {
        $this->eventsParticipes->removeElement($event);
    }
}
