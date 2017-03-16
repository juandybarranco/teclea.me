<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Community
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
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=false, length=300)
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="string", length=15, nullable=false)
     *
     * @var string
     */
    protected $privacy = 'public';

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
    protected $isSuspended = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isDeleted = false;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="userCommunityCreator")
     *
     * @var User
     */
    protected $communityCreator;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="userCommunityAdmin")
     *
     * @var User
     */
    protected $admin;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserCommunity", mappedBy="community")
     *
     * @var UserCommunity
     */
    protected $userCommunity;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CommunityInvitations", mappedBy="community")
     *
     * @var CommunityInvitations
     */
    protected $CI;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="community")
     *
     * @var Message
     */
    protected $message;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReportsAdmin", mappedBy="communityReported")
     *
     * @var ReportsAdmin
     */
    protected $communityReportsAdmin;

    /**
     * @return mixed
     */
    public function getCommunityReportsAdmin()
    {
        return $this->communityReportsAdmin;
    }

    /**
     * @param mixed $communityReportsAdmin
     */
    public function setCommunityReportsAdmin($communityReportsAdmin)
    {
        $this->communityReportsAdmin = $communityReportsAdmin;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getCI()
    {
        return $this->CI;
    }

    /**
     * @param mixed $CI
     */
    public function setCI($CI)
    {
        $this->CI = $CI;
    }

    /**
     * @return mixed
     */
    public function getUserCommunity()
    {
        return $this->userCommunity;
    }

    /**
     * @param mixed $userCommunity
     */
    public function setUserCommunity($userCommunity)
    {
        $this->userCommunity = $userCommunity;
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return string
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }

    /**
     * @param string $privacy
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
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
    public function isIsSuspended()
    {
        return $this->isSuspended;
    }

    /**
     * @param bool $isSuspended
     */
    public function setIsSuspended($isSuspended)
    {
        $this->isSuspended = $isSuspended;
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
     * @return User
     */
    public function getCommunityCreator()
    {
        return $this->communityCreator;
    }

    /**
     * @param User $communityCreator
     */
    public function setCommunityCreator($communityCreator)
    {
        $this->communityCreator = $communityCreator;
    }

    /**
     * @return User
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param User $admin
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

}