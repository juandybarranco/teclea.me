<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Message
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="message")
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Community", inversedBy="message")
     *
     * @var Community
     */
    protected $community;

    /**
     * @ORM\Column(type="string", nullable=false, length=1024)
     *
     * @var string
     */
    protected $message;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $image;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $location;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $IP;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isActive = true;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isBlock = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isDeleted = false;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\URLShort", mappedBy="message")
     *
     * @var URLShort
     */
    protected $urlShort;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReportsAdmin", mappedBy="messageReported")
     *
     * @var ReportsAdmin
     */
    protected $messageReportsAdmin;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReportCommunity", mappedBy="messageReported")
     *
     * @var ReportCommunity
     */
    protected $messageReportCommunity;

    /**
     * @return ReportCommunity
     */
    public function getMessageReportCommunity()
    {
        return $this->messageReportCommunity;
    }

    /**
     * @param ReportCommunity $messageReportCommunity
     */
    public function setMessageReportCommunity($messageReportCommunity)
    {
        $this->messageReportCommunity = $messageReportCommunity;
    }

    /**
     * @return mixed
     */
    public function getMessageReportsAdmin()
    {
        return $this->messageReportsAdmin;
    }

    /**
     * @param mixed $messageReportsAdmin
     */
    public function setMessageReportsAdmin($messageReportsAdmin)
    {
        $this->messageReportsAdmin = $messageReportsAdmin;
    }

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
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return Community
     */
    public function getCommunity()
    {
        return $this->community;
    }

    /**
     * @param Community $community
     */
    public function setCommunity($community)
    {
        $this->community = $community;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
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
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getIP()
    {
        return $this->IP;
    }

    /**
     * @param string $IP
     */
    public function setIP($IP)
    {
        $this->IP = $IP;
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

    /**
     * @return bool
     */
    public function isIsBlock()
    {
        return $this->isBlock;
    }

    /**
     * @param bool $isBlock
     */
    public function setIsBlock($isBlock)
    {
        $this->isBlock = $isBlock;
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
     * @return mixed
     */
    public function getUrlShort()
    {
        return $this->urlShort;
    }

    /**
     * @param mixed $urlShort
     */
    public function setUrlShort($urlShort)
    {
        $this->urlShort = $urlShort;
    }

}