<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PasteRepository")
 * @ORM\Table(name="paste")
 */
class Paste
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max="128", charset="UTF-8")
     * @ORM\Column(type="text", nullable=false, length=128)
     */
    private $title;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max="2500000", maxMessage="This log is too long. It should be under 250KB worth of text.")
     * @ORM\Column(type="text", nullable=false)
     */
    private $message;

    /**
     * @ORM\Column(type="text", nullable=true, length=64)
     */
    private $encryption_key;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $created;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":0})
     */
    private $encrypted;

    /**
     * @Assert\Ip(version="all")
     * @ORM\Column(type="text", length=45, nullable=false)
     */
    private $ip;

    /**
     * @ORM\Column(type="smallint", length=1, nullable=false, options={"default":1})
     */
    private $status;

    /**
     * @ORM\Column(type="integer", options={"unsigned":true})
     */
    private $filter;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="snippets")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->status = PasteStatus::ACTIVE;
        $this->filter = 0;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return string
     */
    public function getEncryptionKey()
    {
        return $this->encryption_key;
    }

    /**
     * @param $encryption_key
     * @return $this
     */
    public function setEncryptionKey($encryption_key)
    {
        $this->encryption_key = $encryption_key;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
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

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEncrypted()
    {
        return $this->encrypted;
    }

    /**
     * @param mixed $encrypted
     */
    public function setEncrypted($encrypted)
    {
        $this->encrypted = $encrypted;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param mixed $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
