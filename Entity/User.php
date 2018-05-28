<?php

namespace reviewProject\Entity;

use reviewProject\Enum\UserStatus;

/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 28.05.18
 * Time: 14:17
 */
abstract class User
{
    /**
     * @var int $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var UserStatus $status
     */
    private $status;

    /**
     * @return int
     */
    public function getId() : int

    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return UserStatus
     */
    public function getStatus() : UserStatus
    {
        return $this->status;
    }

    /**
     * @param UserStatus $status
     */
    public function setStatus(UserStatus $status) : void
    {
        $this->status = $status;
    }

}