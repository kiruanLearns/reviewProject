<?php

namespace reviewProject\Model\Validator;

interface CloseAccountValidatorInterface
{
   public function canBeClosed();

   /** @var \Exception[]  */
   public function getErrors();
}