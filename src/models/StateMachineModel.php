<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 25.02.18
 * Time: 16:39
 */

namespace KotaShade\Yii2StateMachine\models;


class StateMachineModel extends StateMachine
{
    protected function getTransitionAForState($stateE, $actionE)
    {
        // TODO: Implement getTransitionAForState() method.
    }

    protected function getObjectState($objE)
    {
        // TODO: Implement getObjectState() method.
    }

    /**
     * устанавливает состояние у объекта
     * @param object $objE
     * @param object $stateE
     * @return object
     */
    protected function setObjectState($objE, $stateE)
    {
        $setter = $this->stateAttribute;
        $objE->$setter($stateE);
//TODO это для доктрины
//        $setStateMethod = 'set' . ucfirst($this->stateAttribute);
//        if (method_exists($objE, $setStateMethod) == false) {
//            throw new ExceptionNS\InvalidStateGetter('отсутсвует метод '
//                . $setStateMethod . ' у класса ' . get_class($objE));
//        }
//        $objE->$setStateMethod($stateE);
//        return $objE;
    }

}