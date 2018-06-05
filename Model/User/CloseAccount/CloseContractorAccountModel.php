<?php

namespace reviewProject\Model;

use reviewProject\Entity\User;
use reviewProject\Enum\UserStatus;
use reviewProject\Model\Validator\CloseAccountValidatorInterface;
use reviewProject\Model\Order\CancelOrderModel;

class CloseContractorAccountModel implements CloseAccountModelInterface
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
     * @throws PropelException
     * @throws Exception
     */
    public function request()
    {
        $this->cancelOrderModel->cancelContractorOrders();

        if ($this->validator->canBeClosed() === false) {
            $errorMsg = '';
            /** @var \Exception $error */
            foreach ($this->validator->getErrors() as $error) {
                $errorMsg .= $error->getMessage().'<br/>';
            }
            throw new CannotCloseAccountException($errorMsg);
        }
        $this->user->setStatus(UserStatus::REQUESTED_CLOSE_ACCOUNT);
        $this->user->save();

        $this->logEvent('CONTRACTOR_ACCOUNT_CLOSED_REQUESTED');

        $this->sendMailToContractor();
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
                    'The contractor\'s status  must be %d current is: %d contractor:%d',
                    UserPeer::STATUS_REQUESTED_CLOSE_ACCOUNT,
                    $this->user->getStatus(),
                    $this->user->getId()
                )
            );
        }

        if ($this->validator->canBeClosed() === false) {
            throw new CannotCloseAccountException('The contractor\'s account %d can not be closed', $this->user->getId());
        }

        $this->user->setStatus(UserStatus::ACCOUNT_CLOSED);

        $this->logEvent('CONTRACTOR_ACCOUNT_CLOSED');
    }


    protected function logEvent($event) : void
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

    protected function sendMailToContractor() : void
    {
        //@todo send mail
    }

}