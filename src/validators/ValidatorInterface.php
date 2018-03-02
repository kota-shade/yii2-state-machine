<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 02.03.18
 * Time: 11:40
 */

namespace KotaShade\Yii2StateMachine\validators;


interface ValidatorInterface
{
    /**
     * @param object $obj - this object is processed by state machine
     * @param array $data - extra data for validation
     * @return bool
     */
    public function validate($obj, array $data=[]);

    /**
     * @return array
     */
    public function getMessages();
}