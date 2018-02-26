<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 26.02.18
 * Time: 15:36
 */

namespace KotaShade\Yii2StateMachine\exceptions;


use Throwable;
use KotaShade\Yii2StateMachine\models\TransitionAInterface;

class InvalidTransition extends StateMachineException
{
    public function __construct(TransitionAInterface $trAE, int $code = 500, Throwable $previous = null)
    {
        $name = get_class($trAE);
        $msg = sprintf("need default target in transitionB for %s(id=%s)", $name, $trAE->getId());

        parent::__construct($msg, $code, $previous);
    }
}