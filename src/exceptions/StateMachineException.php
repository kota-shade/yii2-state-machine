<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 26.02.18
 * Time: 13:49
 */

namespace KotaShade\Yii2StateMachine\exceptions;


use Throwable;

class StateMachineException extends \RuntimeException
{
    public function __construct(string $message = "State machine runtime error", int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
