<?php

namespace Entities;

use Doctrine\Mapping as ORM;

/**
 * Reservation
 *
 * @Table(name="reservations", indexes={@Index(name="session_id", columns={"session_id"}), @Index(name="room_id", columns={"room_id"})})
 * @Entity
 */
class Reservation
{
    /**
     * @var integer
     *
     * @Column(name="id", type="integer", nullable=false)
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @Column(name="start_date", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @var \Entities\Session
     *
     * @ManyToOne(targetEntity="Entities\Session", inversedBy="reservations")
     * @JoinColumns({
     *   @JoinColumn(name="session_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $session;

    /**
     * @var \Entities\Room
     *
     * @ManyToOne(targetEntity="Entities\Room")
     * @JoinColumns({
     *   @JoinColumn(name="room_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $room;

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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Reservation
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    
        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Reservation
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    
        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set session
     *
     * @param \Entities\Session $session
     * @return Reservation
     */
    public function setSession(\Entities\Session $session = null)
    {
        $this->session = $session;
    
        return $this;
    }

    /**
     * Get session
     *
     * @return \Entities\Session 
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set room
     *
     * @param \Entities\Room $room
     * @return Reservation
     */
    public function setRoom(\Entities\Room $room = null)
    {
        $this->room = $room;
    
        return $this;
    }

    /**
     * Get room
     *
     * @return \Entities\Room 
     */
    public function getRoom()
    {
        return $this->room;
    }
}
