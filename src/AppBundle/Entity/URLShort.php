<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class URLShort
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
    protected $originalURL;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $shortedURL;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Message", inversedBy="urlShort")
     *
     * @var Message
     */
    protected $message;

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
    public function getOriginalURL()
    {
        return $this->originalURL;
    }

    /**
     * @param string $originalURL
     */
    public function setOriginalURL($originalURL)
    {
        $this->originalURL = $originalURL;
    }

    /**
     * @return string
     */
    public function getShortedURL()
    {
        return $this->shortedURL;
    }

    /**
     * @param string $shortedURL
     */
    public function setShortedURL($shortedURL)
    {
        $this->shortedURL = $shortedURL;
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

}