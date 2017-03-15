<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Follow
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="userFollower")
     *
     * @var User
     */
    protected $follower;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="userFollowing")
     *
     * @var User
     */
    protected $following;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $followDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $acceptedDate;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isAccepted;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isDeleted = false;

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
    public function getFollower()
    {
        return $this->follower;
    }

    /**
     * @param User $follower
     */
    public function setFollower($follower)
    {
        $this->follower = $follower;
    }

    /**
     * @return User
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * @param User $following
     */
    public function setFollowing($following)
    {
        $this->following = $following;
    }

    /**
     * @return \DateTime
     */
    public function getFollowDate()
    {
        return $this->followDate;
    }

    /**
     * @param \DateTime $followDate
     */
    public function setFollowDate($followDate)
    {
        $this->followDate = $followDate;
    }

    /**
     * @return \DateTime
     */
    public function getAcceptedDate()
    {
        return $this->acceptedDate;
    }

    /**
     * @param \DateTime $acceptedDate
     */
    public function setAcceptedDate($acceptedDate)
    {
        $this->acceptedDate = $acceptedDate;
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

}