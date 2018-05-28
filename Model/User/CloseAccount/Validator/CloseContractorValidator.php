<?php

namespace reviewProject\Model\Validator;

use reviewProject\Entity\User;
use reviewProject\Model\CannotCloseAccountException;

class CloseContractorValidator implements CloseAccountValidatorInterface
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
     * @throws CannotCloseAccountException
     */
    public function canBeClosed()
    {
        if ($this->hasActiveOrders()) {
            $this->errors[] = new CannotCloseAccountException('You still have running orders. We must wait until those orders are accepted, rejected or cancelled before we can close your account.');
        }

        if ($this->hasPositiveBalance()) {
            $this->errors[] = new CannotCloseAccountException('You have money on your account. Please request a payment so that we can close your account.');
        }

        if ($this->hasNegativeBalance()) {
            $this->errors[] = new CannotCloseAccountException('You have open fees on your account. Please contact our support so that we can close your account.');
        }

        if (count($this->errors) === 0) {
            return true;
        }

        return false;
    }

    public function getErrors()
    {
        return $this->errors;
    }


    private function getBalance()
    {
        /** @var Currency $currency */
        $currency = CurrencyPeer::getByPlatformId($this->user->getPlattformId());
        /** @var DateTime $now */
        $now = new DateTime();

        return AccountingPeer::getUserBalancesByDateAndCurrencyId(
            $this->user->getId(),
            $now,
            $currency->getId()
        );
    }

    private function hasPositiveBalance()
    {
        /** @var flaot $balance */
        $balance = $this->getBalance();
        return  $balance > 0;
    }

    private function hasNegativeBalance()
    {
        return $this->getBalance() < 0;
    }

    private function hasActiveOrders()
    {
        $activeStatuses = array(
            ProjectsPeer::STATUS_PROCESSING,
            ProjectsPeer::STATUS_SUBMITTED,
            ProjectsPeer::STATUS_RESUBMITTED,
            ProjectsPeer::STATUS_REJECTION_REQUESTED,
        );
        $oProjectCriteria = new Criteria;
        $oProjectCriteria->clearSelectColumns();
        $oProjectCriteria->addSelectColumn('count(id) AS nb_orders');
        $oProjectCriteria->add(ProjectsPeer::ROW_DELETED, 1, Criteria::NOT_EQUAL);
        $oProjectCriteria->add(ProjectsPeer::PARENT_ID, 0, Criteria::NOT_EQUAL);
        $oProjectCriteria->add(ProjectsPeer::PLATTFORM_ID, range(1, 15), Criteria::IN);
        $oProjectCriteria->add(ProjectsPeer::CURRENCY_ID, range(1, 5), Criteria::IN);
        $oProjectCriteria->add(ProjectsPeer::STATUS, $activeStatuses, Criteria::IN);
        $oProjectCriteria->add(ProjectsPeer::ORDER_GIVEN_TO, $this->user->getId());
        $oStatement = ProjectsPeer::doSelectStmt($oProjectCriteria);
        while ($aRow = $oStatement->fetch(PDO::FETCH_ASSOC)) {
            $iNbOpen = $aRow['nb_orders'];
        }
        if ($iNbOpen === null) {
            throw new Exception('could not find the nb of active orders for user: %d', $this->user->getId());
        }

        return $iNbOpen > 0;

    }
}