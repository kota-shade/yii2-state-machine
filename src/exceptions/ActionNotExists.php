<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 26.02.18
 * Time: 13:49
 */
namespace KotaShade\Yii2StateMachine\exceptions;

use Throwable;

class ActionNotExists extends StateMachineException
{
    public function __construct($action, int $code = 500, Throwable $previous = null)
    {
        parent::__construct("action doesn't exists, action=". $action, $code, $previous);
    }
}