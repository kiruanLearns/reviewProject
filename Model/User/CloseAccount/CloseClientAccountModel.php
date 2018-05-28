<?php

namespace reviewProject\Model;

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
     * @throws PropelException
     */
    public function request()
    {
        $this->cancelOrderModel->cancelAllInactive();

        if ($this->validator->canBeClosed() === false) {
            throw new CannotCloseAccountException('Your account can not be closed before all your running orders are completed.');
        }
        $this->user->setContractorStatus(UserPeer::STATUS_REQUESTED_CLOSE_ACCOUNT);
        $this->user->save();

        $this->logEvent('CLIENT_ACCOUNT_CLOSED_REQUESTED');

        $this->sendMailToClient();
        $this->sendMailToGcSupport();

    }

    /**
     * @throws Exception
     */
    public function close()
    {
        if ($this->user->getContractorStatus() !== UserPeer::STATUS_REQUESTED_CLOSE_ACCOUNT) {
            throw new CannotCloseAccountException(
                'The client\'s status  must be %d current is: %d client:%d',
                UserPeer::STATUS_REQUESTED_CLOSE_ACCOUNT,
                $this->user->getContractorStatus(),
                $this->user->getId()
            );
        }

        if ($this->validator->canBeClosed() === false) {
            throw new CannotCloseAccountException('The client\'s account %d can not be closed', $this->user->getId());
        }

        $this->user->setContractorStatus(UserPeer::STATUS_SELF_DELETED);
        $this->user->setIsPublished(0);
        $this->user->setWatchlist(0);
        $this->user->save();

        $this->logEvent('CLIENT_ACCOUNT_CLOSED');
    }


    protected function logEvent($event)
    {
        CBLogger::setEvent($event);
        CBLogger::setRefId($this->user->getId());
        CBLogger::setRefTbl('cb_user');
        CBLogger::setUserType(strtoupper(UserPeer::TYPE_CLIENT));
        CBLogger::loggen();
    }

    protected function sendMailToGcSupport()
    {
        try { //send email to user

            $cbMailer = new CBMail(
                sfConfig::get('app_mails_account_deletion_address', 'account-deletion@greatcontent.com'),
                sfConfig::get('app_mails_account_deletion_address', 'account-deletion@greatcontent.com'),
                false, false, true);
            $cbMailer->setRecipientCulture('en_GB');
            $cbMailer->sendMail(
                'SEND_EMAIL_TO_GC_ACCOUNT_DELETION',
                array(

                    'USER_TYPE' => $this->user->getUserType(),
                    'USER_FULL_NAME' => sprintf('%s %s', $this->user->getUserFname(), $this->user->getUserLname()),
                    'USER_ID' => $this->user->getId(),
                )
            );
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function sendMailToClient()
    {
        try {
            //send email to GC email
            $cbMailer = new CBMail($this->user, sfConfig::get('app_mails_account_deletion_address', 'account-deletion@greatcontent.com'), null, null, false);
            $cbMailer->setRecipientCulture($this->user->getCultureForPDF());

            $cbMailer->sendMail(
                'SEND_EMAIL_TO_USER_ACCOUNT_DELETION',
                array(
                    'USER_FULL_NAME' => sprintf('%s %s', $this->user->getUserFname(), $this->user->getUserLname()),
                )
            );


        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}