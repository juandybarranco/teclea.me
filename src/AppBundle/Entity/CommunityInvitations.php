<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CommunityInvitations
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="CISender")
     *
     * @var User
     */
    protected $sender;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="CIRecipient")
     *
     * @var User
     */
    protected $recipient;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", inversedBy="CI")
     *
     * @var Community
     */
    protected $community;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isAccepted = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isDeleted = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isUserRequest;

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
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param mixed $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return User
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param User $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return mixed
     */
    public function getCommunity()
    {
        return $this->community;
    }

    /**
     * @param mixed $community
     */
    public function setCommunity($community)
    {
        $this->community = $community;
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
     * @return bool
     */
    public function isIsAccepted()
    {
        return $this->isAccepted;
    }

    /**
     * @param bool $isAccepted
     */
    public function setIsAccepted($isAccepted)
    {
        $this->isAccepted = $isAccepted;
    }

    /**
     * @return bool
     */
    public function isIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @param bool $isDeleted
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
    }

    /**
     * @return bool
     */
    public function isIsUserRequest()
    {
        return $this->isUserRequest;
    }

    /**
     * @param bool $isUserRequest
     */
    public function setIsUserRequest($isUserRequest)
    {
        $this->isUserRequest = $isUserRequest;
    }

}