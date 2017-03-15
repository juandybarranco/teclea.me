<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ReportCommunity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Message", inversedBy="messageReportCommunity")
     *
     * @var Message
     */
    protected $messageReported;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="informerReportCommunity")
     *
     * @var User
     */
    protected $informer;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\Column(type="string", nullable=false, length=150)
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isActive = true;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Message
     */
    public function getMessageReported()
    {
        return $this->messageReported;
    }

    /**
     * @param Message $messageReported
     */
    public function setMessageReported($messageReported)
    {
        $this->messageReported = $messageReported;
    }

    /**
     * @return User
     */
    public function getInformer()
    {
        return $this->informer;
    }

    /**
     * @param User $informer
     */
    public function setInformer($informer)
    {
        $this->informer = $informer;
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
     * @return bool
     */
    public function isIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

}