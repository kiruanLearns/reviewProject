<?php

namespace reviewProject\Model\Validator;

use reviewProject\Entity\User;

class CloseClientValidator implements CloseAccountValidatorInterface
{

    /** @var User $user */
    protected $user;

    /** @var \Exception[] */
    protected $errors;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->errors = array();
    }


    /**
     * @return bool
     */
    public function canBeClosed(): bool
    {
        return false;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        $this->errors;
    }


}