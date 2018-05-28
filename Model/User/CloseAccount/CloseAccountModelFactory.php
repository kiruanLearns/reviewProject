<?php

namespace reviewProject\Model;

use reviewProject\Entity\User;
use reviewProject\Entity\Client;
use reviewProject\Entity\Contractor;
use reviewProject\Model\Validator\CloseClientValidator;
use reviewProject\Model\Validator\CloseContractorValidator;
use reviewProject\Model\
class CloseAccountModelFactory
{
    /**
     * @param User $user
     * @return CloseClientAccountModel|CloseContractorAccountModel
     */
    public function getModel(User $user)
    {
        if ($user instanceof Client) {
            return new CloseClientAccountModel($user, new CloseClientValidator($user), new CancelOrderModel($user));
        }

        if ($user instanceof Contractor) {
            return new CloseContractorAccountModel($user, new CloseContractorValidator($user), new CancelOrderModel($user));
        }

        throw new InvalidArgumentException(sprintf('Invalid user provided. id:%d', $user->getId()));

    }

}