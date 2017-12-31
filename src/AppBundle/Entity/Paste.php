<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PasteRepository")
 * @ORM\Table(name="paste")
 */
class Paste
{
    const TITLE_LENGTH = 128;
    const MESSAGE_LENGTH = 2500000;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=Paste::TITLE_LENGTH, maxMessage="This title is too long. It should be under {{ limit }} characters.", charset="UTF-8")
     * @ORM\Column(type="text", nullable=false, length=128)
     */
    private $title;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=Paste::MESSAGE_LENGTH, maxMessage="This log is too long. It should be under ~250KB worth of text.")
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
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $private_message_filters;

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
        $this->private_message_filters = [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getEncryptionKey()
    {
        return $this->encryption_key;
    }

    public function setEncryptionKey(string $encryption_key): self
    {
        $this->encryption_key = $encryption_key;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getEncrypted()
    {
        return $this->encrypted;
    }

    public function setEncrypted(bool $encrypted): self
    {
        $this->encrypted = $encrypted;

        return $this;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setFilter(int $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function getPrivateMessageFilters(): array
    {
        return $this->private_message_filters;
    }

    public function setPrivateMessageFilters(array $privateMessageFilters): self
    {
        $this->private_message_filters = $privateMessageFilters;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }
}
