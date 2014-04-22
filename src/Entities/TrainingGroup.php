<?php

namespace Entities;

use Doctrine\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * TrainingGroup
 *
 * @Table(name="groups", uniqueConstraints={@UniqueConstraint(name="name", columns={"name"})})
 * @Entity(repositoryClass="Repositories\TrainingGroupRepository")
 */
class TrainingGroup
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
     * @Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany(targetEntity="Entities\Training", mappedBy="group")
     */
    private $trainings;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->trainings = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return TrainingGroup
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

    /**
     * Add trainings
     *
     * @param \Entities\Training $trainings
     * @return TrainingGroup
     */
    public function addTraining(\Entities\Training $trainings)
    {
        $this->trainings[] = $trainings;
    
        return $this;
    }

    /**
     * Remove trainings
     *
     * @param \Entities\Training $trainings
     */
    public function removeTraining(\Entities\Training $trainings)
    {
        $this->trainings->removeElement($trainings);
    }

    /**
     * Get trainings
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTrainings()
    {
        return $this->trainings;
    }
}
