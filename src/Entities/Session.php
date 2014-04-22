<?php

namespace Entities;

use Doctrine\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Session
 *
 * @Table(name="sessions", indexes={@Index(name="training_id", columns={"training_id"})})
 * @Entity(repositoryClass="Repositories\SessionRepository")
 */
class Session
{
    const LEVEL_BEGINNER = 0;
    const LEVEL_INTERMEDIATE = 1;
    const LEVEL_ADVANCED = 2;

    /**
     * @var integer
     *
     * @Column(name="id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @Column(name="max_users", type="integer", nullable=true)
     */
    private $maxUsers;

    /**
     * @var string
     *
     * @Column(name="comment", type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @var integer
     *
     * @Column(name="level", type="integer", nullable=false)
     */
    private $level = \Entities\Session::LEVEL_INTERMEDIATE;

    /**
     * @var boolean
     *
     * @Column(name="is_finished", type="boolean", nullable=false)
     */
    private $isFinished = false;

    /**
     * @var \Entities\Training
     *
     * @ManyToOne(targetEntity="Entities\Training", inversedBy="sessions")
     * @JoinColumns({
     *   @JoinColumn(name="training_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $training;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany(targetEntity="Entities\SessionRequest", mappedBy="session", cascade={"remove"})
     */
    private $requests;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany(targetEntity="Entities\Reservation", mappedBy="session", cascade={"remove"})
     */
    private $reservations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requests = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set maxUsers
     *
     * @param integer $maxUsers
     * @return Session
     */
    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    
        return $this;
    }

    /**
     * Get maxUsers
     *
     * @return integer 
     */
    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Session
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set level
     *
     * @param integer $level
     * @return Session
     */
    public function setLevel($level)
    {
        $this->level = $level;
    
        return $this;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set isFinished
     *
     * @param boolean $isFinished
     * @return Session
     */
    public function setIsFinished($isFinished)
    {
        $this->isFinished = $isFinished;
    
        return $this;
    }

    /**
     * Get isFinished
     *
     * @return boolean 
     */
    public function getIsFinished()
    {
        return $this->isFinished;
    }

    /**
     * Set training
     *
     * @param \Entities\Training $training
     * @return Session
     */
    public function setTraining(\Entities\Training $training = null)
    {
        $this->training = $training;
    
        return $this;
    }

    /**
     * Get training
     *
     * @return \Entities\Training 
     */
    public function getTraining()
    {
        return $this->training;
    }

    /**
     * Add requests
     *
     * @param \Entities\SessionRequest $requests
     * @return Session
     */
    public function addRequest(\Entities\SessionRequest $requests)
    {
        $this->requests[] = $requests;
    
        return $this;
    }

    /**
     * Remove requests
     *
     * @param \Entities\SessionRequest $requests
     */
    public function removeRequest(\Entities\SessionRequest $requests)
    {
        $this->requests->removeElement($requests);
    }

    /**
     * Get requests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Add reservations
     *
     * @param \Entities\Reservation $reservations
     * @return Session
     */
    public function addReservation(\Entities\Reservation $reservations)
    {
        $this->reservations[] = $reservations;
    
        return $this;
    }

    /**
     * Remove reservations
     *
     * @param \Entities\Reservation $reservations
     */
    public function removeReservation(\Entities\Reservation $reservations)
    {
        $this->reservations->removeElement($reservations);
    }

    /**
     * Get reservations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReservations()
    {
        return $this->reservations;
    }
}
