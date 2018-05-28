<?php

namespace reviewProject\Model;

use reviewProject\Entity\User;
use reviewProject\Enum\UserStatus;
use reviewProject\Model\Validator\CloseAccountValidatorInterface;
use reviewProject\Model\Order\CancelOrderModel;

class CloseClientAccountModel implements CloseAccountModelInterface
{
    /** @var User $user */
    protected $user;

    /** @var CloseAccountValidatorInterface $validator */
    protected $validator;

    /** @var CancelOrderModel $cancelOrderModel */
    protected $cancelOrderModel;

    public function __construct(User $user, CloseAccountValidatorInterface $validator, CancelOrderModel $cancelOrderModel)
    {
        $this->user = $user;
        $this->validator = $validator;
        $this->cancelOrderModel = $cancelOrderModel;
    }

    /**
     * @throws CannotCloseAccountException
     */
    public function request()
    {
        $this->cancelOrderModel->cancelAllInactive();

        if ($this->validator->canBeClosed() === false) {
            throw new CannotCloseAccountException('Your account can not be closed before all your running orders are completed.');
        }
        $this->user->setContractorStatus(UserPeer::STATUS_REQUESTED_CLOSE_ACCOUNT);


        $this->logEvent('CLIENT_ACCOUNT_CLOSED_REQUESTED');

        $this->sendMailToClient();
        $this->sendMailToGcSupport();

    }

    /**
     * @throws CannotCloseAccountException
     */
    public function close()
    {
        if ($this->user->getStatus() !== UserStatus::REQUESTED_CLOSE_ACCOUNT) {
            throw new CannotCloseAccountException(
                sprintf(
                    'The client\'s status  must be %d current is: %d client:%d',
                    UserStatus::REQUESTED_CLOSE_ACCOUNT,
                    $this->user->getContractorStatus(),
                    $this->user->getId()
                )
            );
        }

        if ($this->validator->canBeClosed() === false) {
            throw new CannotCloseAccountException('The client\'s account %d can not be closed', $this->user->getId());
        }

        $this->user->setStatus(UserStatus::ACCOUNT_CLOSED);

        $this->logEvent('CLIENT_ACCOUNT_CLOSED');
    }


    protected function logEvent($event): void
    {
        //@todo log event
    }

    /**
     * @throws Exception
     */
    protected function sendMailToGcSupport(): void
    {
        //@todo send mail
    }

    protected function sendMailToClient(): void
    {
        //@todo send mail
    }

}