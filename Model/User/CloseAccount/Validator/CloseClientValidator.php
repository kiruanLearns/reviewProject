<?php

namespace reviewProject\Model\Validator;

class CloseClientValidator implements CloseAccountValidatorInterface
{

    /** @var User $user */
    protected $user;

    /** @var Exception[] */
    protected $errors;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->errors = array();
    }


    /**
     * @return bool
     * @throws PropelException
     */
    public function canBeClosed()
    {
        return !ProjectsPeer::hasActiveOrders($this->user);
    }

    public function getErrors()
    {
        $this->errors;
    }


}