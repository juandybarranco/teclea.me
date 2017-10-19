<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @UniqueEntity(fields={"username"}, errorPath="username", message="El nombre de usuario ya está en uso.")
 * @UniqueEntity(fields={"email"}, errorPath="email", message="La dirección de correo electrónico ya está en uso.")
 */
class User implements UserInterface
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
     * @ORM\Column(type="string", length=25, unique=true, nullable=false)
     *
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true, length=60)
     * @Assert\Email()
     *
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $signUpDate;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $country;

    /**
     * @ORM\Column(type="string", length=35, nullable=true)
     *
     * @var string
     */
    protected $location;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     *
     * @var string
     */
    protected $personalMessage;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $image;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isPublic = true;

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
    protected $isAdmin = false;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User", mappedBy="referred")
     *
     * @var User
     */
    protected $userRef;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User", inversedBy="userRef")
     *
     * @var User
     */
    protected $referred;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Community", mappedBy="communityCreator")
     *
     * @var Community
     */
    protected $userCommunityCreator;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Community", mappedBy="admin")
     *
     * @var Community
     */
    protected $userCommunityAdmin;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserCommunity", mappedBy="user")
     *
     * @var UserCommunity
     */
    protected $userCommunity;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CommunityInvitations", mappedBy="sender")
     *
     * @var CommunityInvitations
     */
    protected $CISender;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CommunityInvitations", mappedBy="recipient")
     *
     * @var CommunityInvitations
     */
    protected $CIRecipient;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="user")
     *
     * @var Message
     */
    protected $message;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Point", mappedBy="user")
     *
     * @var Point
     */
    protected $point;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReportsAdmin", mappedBy="userReported")
     *
     * @var ReportsAdmin
     */
    protected $userReportsAdmin;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReportsAdmin", mappedBy="informer")
     *
     * @var ReportsAdmin
     */
    protected $informerReportsAdmin;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReportCommunity", mappedBy="informer")
     *
     * @var ReportCommunity
     */
    protected $informerReportCommunity;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Follow", mappedBy="follower")
     *
     * @var Follow
     */
    protected $userFollower;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Follow", mappedBy="following")
     *
     * @var Follow
     */
    protected $userFollowing;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PM", mappedBy="sender")
     *
     * @var PM
     */
    protected $PMSender;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PM", mappedBy="recipient")
     *
     * @var PM
     */
    protected $PMRecipient;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Notification", mappedBy="user")
     *
     * @var Notification
     */
    protected $notification;

    /**
     * @return mixed
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param mixed $notification
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
    }

    /**
     * @return mixed
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * @param mixed $isAdmin
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return PM
     */
    public function getPMSender()
    {
        return $this->PMSender;
    }

    /**
     * @param PM $PMSender
     */
    public function setPMSender($PMSender)
    {
        $this->PMSender = $PMSender;
    }

    /**
     * @return PM
     */
    public function getPMRecipient()
    {
        return $this->PMRecipient;
    }

    /**
     * @param PM $PMRecipient
     */
    public function setPMRecipient($PMRecipient)
    {
        $this->PMRecipient = $PMRecipient;
    }

    /**
     * @return mixed
     */
    public function getInformerReportCommunity()
    {
        return $this->informerReportCommunity;
    }

    /**
     * @param mixed $informerReportCommunity
     */
    public function setInformerReportCommunity($informerReportCommunity)
    {
        $this->informerReportCommunity = $informerReportCommunity;
    }

    /**
     * @return mixed
     */
    public function getUserFollower()
    {
        return $this->userFollower;
    }

    /**
     * @param mixed $userFollower
     */
    public function setUserFollower($userFollower)
    {
        $this->userFollower = $userFollower;
    }

    /**
     * @return Follow
     */
    public function getUserFollowing()
    {
        return $this->userFollowing;
    }

    /**
     * @param Follow $userFollowing
     */
    public function setUserFollowing($userFollowing)
    {
        $this->userFollowing = $userFollowing;
    }

    /**
     * @return mixed
     */
    public function getUserReportsAdmin()
    {
        return $this->userReportsAdmin;
    }

    /**
     * @param mixed $userReportsAdmin
     */
    public function setUserReportsAdmin($userReportsAdmin)
    {
        $this->userReportsAdmin = $userReportsAdmin;
    }

    /**
     * @return ReportsAdmin
     */
    public function getInformerReportsAdmin()
    {
        return $this->informerReportsAdmin;
    }

    /**
     * @param ReportsAdmin $informerReportsAdmin
     */
    public function setInformerReportsAdmin($informerReportsAdmin)
    {
        $this->informerReportsAdmin = $informerReportsAdmin;
    }

    /**
     * @return mixed
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param mixed $point
     */
    public function setPoint($point)
    {
        $this->point = $point;
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
    public function getCISender()
    {
        return $this->CISender;
    }

    /**
     * @param mixed $CISender
     */
    public function setCISender($CISender)
    {
        $this->CISender = $CISender;
    }

    /**
     * @return CommunityInvitations
     */
    public function getCIRecipient()
    {
        return $this->CIRecipient;
    }

    /**
     * @param CommunityInvitations $CIRecipient
     */
    public function setCIRecipient($CIRecipient)
    {
        $this->CIRecipient = $CIRecipient;
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
     * @return mixed
     */
    public function getUserCommunityCreator()
    {
        return $this->userCommunityCreator;
    }

    /**
     * @param mixed $userCommunityCreator
     */
    public function setUserCommunityCreator($userCommunityCreator)
    {
        $this->userCommunityCreator = $userCommunityCreator;
    }

    /**
     * @return mixed
     */
    public function getUserCommunityAdmin()
    {
        return $this->userCommunityAdmin;
    }

    /**
     * @param mixed $userCommunityAdmin
     */
    public function setUserCommunityAdmin($userCommunityAdmin)
    {
        $this->userCommunityAdmin = $userCommunityAdmin;
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
     * @return mixed
     */
    public function getSignUpDate()
    {
        return $this->signUpDate;
    }

    /**
     * @param mixed $signUpDate
     */
    public function setSignUpDate($signUpDate)
    {
        $this->signUpDate = $signUpDate;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return mixed
     */
    public function getPersonalMessage()
    {
        return $this->personalMessage;
    }

    /**
     * @param mixed $personalMessage
     */
    public function setPersonalMessage($personalMessage)
    {
        $this->personalMessage = $personalMessage;
    }

    /**
     * @return mixed
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * @param mixed $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
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
     * @return mixed
     */
    public function getUserRef()
    {
        return $this->userRef;
    }

    /**
     * @param mixed $userRef
     */
    public function setUserRef($userRef)
    {
        $this->userRef = $userRef;
    }

    /**
     * @return User
     */
    public function getReferred()
    {
        return $this->referred;
    }

    /**
     * @param User $referred
     */
    public function setReferred($referred)
    {
        $this->referred = $referred;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        if($this->getIsAdmin()){
            return array('ROLE_ADMIN');
        }
        return array('ROLE_USER');
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}