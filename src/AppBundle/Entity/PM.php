<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PM
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="PMSender")
     *
     * @var User
     */
    protected $sender;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="PMRecipient")
     *
     * @var User
     */
    protected $recipient;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $subject = 'Sin asunto';

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $message;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isDeletedBySender = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isDeletedByRecipient = false;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var boolean
     */
    protected $isRead = false;

    /**
     * @ORM\OneToMany(targetEntity="PM", mappedBy="reply")
     *
     * @var PM
     */
    protected $PMreply;

    /**
     * @ORM\ManyToOne(targetEntity="PM", inversedBy="PMreply")
     *
     * @var PM
     */
    protected $reply;

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
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
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
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
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
     * @return bool
     */
    public function isIsDeletedBySender()
    {
        return $this->isDeletedBySender;
    }

    /**
     * @param bool $isDeletedBySender
     */
    public function setIsDeletedBySender($isDeletedBySender)
    {
        $this->isDeletedBySender = $isDeletedBySender;
    }

    /**
     * @return bool
     */
    public function isIsDeletedByRecipient()
    {
        return $this->isDeletedByRecipient;
    }

    /**
     * @param bool $isDeletedByRecipient
     */
    public function setIsDeletedByRecipient($isDeletedByRecipient)
    {
        $this->isDeletedByRecipient = $isDeletedByRecipient;
    }

    /**
     * @return bool
     */
    public function isIsRead()
    {
        return $this->isRead;
    }

    /**
     * @param bool $isRead
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;
    }

    /**
     * @return PM
     */
    public function getPMreply()
    {
        return $this->PMreply;
    }

    /**
     * @param PM $PMreply
     */
    public function setPMreply($PMreply)
    {
        $this->PMreply = $PMreply;
    }

    /**
     * @return PM
     */
    public function getReply()
    {
        return $this->reply;
    }

    /**
     * @param PM $reply
     */
    public function setReply($reply)
    {
        $this->reply = $reply;
    }

}