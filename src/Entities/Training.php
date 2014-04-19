<?php

namespace Entities;

use Doctrine\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Training
 *
 * @Table(name="trainings", indexes={@Index(name="group_id", columns={"group_id"})})
 * @Entity(repositoryClass="Repositories\TrainingRepository")
 */
class Training
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
     * @var string
     *
     * @Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @Column(name="details", type="string", length=8192, nullable=true)
     */
    private $details;

    /**
     * @var boolean
     *
     * @Column(name="is_enabled", type="boolean", nullable=false)
     */
    private $isEnabled = false;

    /**
     * @var \Entities\TrainingGroup
     *
     * @ManyToOne(targetEntity="Entities\TrainingGroup", inversedBy="trainings")
     * @JoinColumns({
     *   @JoinColumn(name="group_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $group;

    /**
     * @var \Entities\User
     *
     * @ManyToOne(targetEntity="Entities\User")
     * @JoinColumns({
     *   @JoinColumn(name="owner_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $owner;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ManyToMany(targetEntity="Entities\User", inversedBy="trainings")
     * @JoinTable(name="training_instructors",
     *   joinColumns={
     *     @JoinColumn(name="training_id", referencedColumnName="id", nullable=true)
     *   },
     *   inverseJoinColumns={
     *     @JoinColumn(name="instructor_id", referencedColumnName="id", nullable=true)
     *   }
     * )
     */
    private $instructors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany(targetEntity="Entities\Session", mappedBy="training", cascade={"remove"})
     */
    private $sessions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->instructors = new ArrayCollection();
        $this->sessions = new ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Training
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Training
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
     * Set details
     *
     * @param string $details
     * @return Training
     */
    public function setDetails($details)
    {
        $this->details = $details;
    
        return $this;
    }

    /**
     * Get details
     *
     * @return string 
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     * @return Training
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
    
        return $this;
    }

    /**
     * Get isEnabled
     *
     * @return boolean 
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Set group
     *
     * @param \Entities\TrainingGroup $group
     * @return Training
     */
    public function setGroup(\Entities\TrainingGroup $group = null)
    {
        $this->group = $group;
    
        return $this;
    }

    /**
     * Get group
     *
     * @return \Entities\TrainingGroup 
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set owner
     *
     * @param \Entities\User $owner
     * @return Training
     */
    public function setOwner(\Entities\User $owner = null)
    {
        $this->owner = $owner;
    
        return $this;
    }

    /**
     * Get owner
     *
     * @return \Entities\User 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Add instructor
     *
     * @param \Entities\User $instructor
     * @return Training
     */
    public function addInstructor(\Entities\User $instructor)
    {
        $this->instructors[] = $instructor;
    
        return $this;
    }

    /**
     * Remove instructor
     *
     * @param \Entities\User $instructor
     */
    public function removeInstructor(\Entities\User $instructor)
    {
        $this->instructors->removeElement($instructor);
    }

    /**
     * Get instructors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInstructors()
    {
        return $this->instructors;
    }

    /**
     * Add session
     *
     * @param \Entities\Session $session
     * @return Training
     */
    public function addSession(\Entities\Session $session)
    {
        $this->sessions[] = $session;
    
        return $this;
    }

    /**
     * Remove session
     *
     * @param \Entities\Session $session
     */
    public function removeSession(\Entities\Session $session)
    {
        $this->sessions->removeElement($session);
    }

    /**
     * Get sessions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSessions()
    {
        return $this->sessions;
    }
}
