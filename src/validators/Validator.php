<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 26.02.18
 * Time: 11:32
 */

namespace KotaShade\Yii2StateMachine\validators;

//use yii\validators\Validator as BaseClass;
use yii\base\BaseObject as BaseClass;

/**
 * validators for state machine must implements this interface
 *
 * Interface ValidatorInterface
 * @package KotaShade\Yii2StateMachine\validators
 */
abstract class Validator extends BaseClass
{
    protected $msgList = [];

    /**
     * @param object $obj - this object is processed by state machine
     * @param array $data - extra data for validation
     * @return bool
     */
    abstract public function validate($obj, $data=[]);

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->msgList;
    }

    public function setMessage($msg, $key=null)
    {
        if ($key !== null) {
            $this->msgList[$key] = $msg;
        } else {
            $this->msgList[] = $msg;
        }
    }
}