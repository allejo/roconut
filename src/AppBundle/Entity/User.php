<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use allejo\BZBBAuthenticationBundle\Entity\User as BaseUser;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="smallint", length=1, nullable=false, options={"default":1})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Paste", mappedBy="user")
     */
    private $snippets;

    public function __construct()
    {
        $this->status = UserStatus::ACTIVE;
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
    }

    /**
     * @return mixed
     */
    public function getSnippets()
    {
        return $this->snippets;
    }

    /**
     * @param mixed $snippets
     */
    public function setSnippets($snippets)
    {
        $this->snippets = $snippets;
    }
}
