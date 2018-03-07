<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 24.02.18
 * Time: 21:21
 */
namespace KotaShade\Yii2StateMachine\models;

use Yii;
//use yii\base\Component;
//use yii\base\Model;
use KotaShade\Yii2StateMachine\models\TransitionAInterface;
use KotaShade\Yii2StateMachine\exceptions as ExceptionNS;

trait StateMachine
{

    /**
     * @param object $objE
     * @param string $action
     * @param array $data
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function doAction($objE, $action, array $data = [])
    {
        if (($transitionE = $this->getActionTransition($objE, $action)) == null) {
            throw new ExceptionNS\ActionNotExistsForState($objE, $action);
        }
        $conditionName = $transitionE->getCondition();
        if ($this->checkActionCondition($errors, $conditionName, $objE, $data) == false) {
            throw new ExceptionNS\ActionNotAllowed($objE, $action, $errors);
        }

        if (($transitionBE = $this->getTransitionB($transitionE, $objE, $data)) == null) {
            //пустое действие не предполагающее перехода и функторов.
            return $data;
        }

        $this->doFunctor($transitionBE->getPreFunctor(), $objE, $data);
        $this->setObjectState($objE, $transitionBE->getDst());
        $this->doFunctor($transitionBE->getPostFunctor(), $objE, $data);

        return $data;
    }

    /**
     * Существует ли заданное действие для объекта в текущем состоянии (с учетом всех проверок на доступность действия)
     * @param $objE
     * @param $action
     * @return bool
     */
    public function hasAction($objE, $action, $data=[])
    {
        if (($transitionE = $this->getActionTransition($objE, $action)) == null) {
            return false;
        }
        $conditionName = $transitionE->getCondition();
        if ($conditionName == null) {
            return true;
        }
        if ($this->checkActionCondition($errors, $conditionName, $objE, $data=[]) == false) {
            return false;
        }
        return true;
    }

    /**
     * возвращает транзакцию по имени действия для объекта, если транзакция в принципе существует (без проверки условий)
     * @param $objE
     * @param $action
     * @return TransitionAInterface
     */
    protected function getActionTransition($objE, $action)
    {
        $stateE = $this->getObjectState($objE);
        /** @var $actionE action entity */
        if(($actionE = $this->getActionEntity($action)) == null) {
            throw new ExceptionNS\ActionNotExists($action);
        }

        $transitionE = $this->getTransitionAForState($stateE, $actionE);
        return $transitionE;
    }

    /**
     * @param $stateE
     * @param $actionE
     * @return TransitionAInterface
     */
    abstract protected function getTransitionAForState($stateE, $actionE);
    abstract protected function getObjectState($objE);
    abstract protected function setObjectState($objE, $stateE);
    abstract protected function getActionEntity($action);

    /**
     * @param TransitionAInterface $transitionE
     * @return array|\Traversable
     */
    abstract protected function getTransitionBList(TransitionAInterface $transitionE);

    /**
     * @param TransitionAInterface $transitionE
     * @param $objE
     * @param $data
     * @return TransitionBInterface|null
     */
    protected function getTransitionB(TransitionAInterface $transitionE, $objE, $data)
    {
        $list = $this->getTransitionBList($transitionE);
        if (count($list) == 0) {
            return null;
        }

        usort($list, array($this, 'cmpWeight'));
        /** @var TransitionBInterface $transitionBE */
        foreach($list as $transitionBE) {
            $conditionName = $transitionBE->getCondition();
            if($this->checkActionCondition($errors, $conditionName, $objE, $data)) {
                return $transitionBE;
            }
        }

        throw new ExceptionNS\InvalidTransition($transitionE);
    }

    /**
     * DESC sort order for array
     * @param TransitionBInterface $trA
     * @param TransitionBInterface $trB
     * @return int
     */
    public function cmpWeight(TransitionBInterface $trA, TransitionBInterface $trB)
    {
        if ($trA->getWeight() == null) {
            return 1;
        }
        elseif($trB->getWeight() == null) {
            return -1;
        }
        else {
            return ($trA->getWeight() < $trB->getWeight()) ? 1: -1;
        }
    }

    /**
     * выполняет валидацию по условию $condition
     * @param $validatorMessages
     * @param string $conditionName
     * @param $objE
     * @param array $data
     * @return bool
     */
    protected function checkActionCondition(&$validatorMessages, $conditionName, $objE, $data=[])
    {
        if ($conditionName == '') {
            return true;
        }

        /** @var \KotaShade\Yii2StateMachine\validators\Validator $validator */
        $validator = $this->getCondition($conditionName);
        if (($validator->validate($objE, $data)) == false) {
            $validatorMessages = $validator->getMessages();
            return false;
        }

        return true;
    }

    protected function getCondition($conditionName)
    {
        if (($realName = Yii::getAlias('@'.$conditionName)) == false) {
            $realName = $conditionName;
        }

        $cond = Yii::createObject($realName);
        return $cond;
    }

//    /**
//     * Возвращает
//     * @param string $condition
//     */
//    protected function getConditions($conditionName)
//    {
//        $validators = [];
//        /** @var \yii\validators\Validator $validator */
//        foreach ($this->getValidators() as $validator) {
//            if ($validator->isActive($conditionName)) {
//                $validators[] = $validator;
//            }
//        }
//        return $validators;
//    }


    /**
     * @param $functorName
     * @param $objE
     * @param array &$data
     * @throws \yii\base\InvalidConfigException
     */
    protected function doFunctor($functorName, $objE, array &$data)
    {
        if ($functorName == '') {
            return;
        }
        if (($functor = $this->getFunctor($functorName)) == null) {
            return;
        }
        $functor($objE, $data);
    }

    /**
     * @param string $functorName
     * @return FunctorInterface object
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFunctor($functorName)
    {
        if (($realName = Yii::getAlias('@'.$functorName)) == false) {
            $realName = $functorName;
        }
        /** @var FunctorInterface $functor */
        $functor = Yii::createObject($realName);
        return $functor;
    }
}
