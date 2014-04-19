<?php

namespace Entities;

use Doctrine\Mapping as ORM;

/**
 * SessionRequest
 *
 * @Table(name="session_requests", indexes={@Index(name="user_id", columns={"user_id"}), @Index(name="session_id", columns={"session_id"})})
 * @Entity
 */
class SessionRequest
{
    const STATUS_CREATED = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_REFUSED = 2;

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
     * @Column(name="status", type="integer", nullable=false)
     */
    private $status = \Entities\SessionRequest::STATUS_CREATED;

    /**
     * @var \Entities\User
     *
     * @ManyToOne(targetEntity="Entities\User", inversedBy="requests")
     * @JoinColumns({
     *   @JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $user;

    /**
     * @var \Entities\Session
     *
     * @ManyToOne(targetEntity="Entities\Session", inversedBy="requests")
     * @JoinColumns({
     *   @JoinColumn(name="session_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $session;

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
     * Set status
     *
     * @param integer $status
     * @return SessionRequest
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set user
     *
     * @param \Entities\User $user
     * @return SessionRequest
     */
    public function setUser(\Entities\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Entities\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set session
     *
     * @param \Entities\Session $session
     * @return SessionRequest
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
}
