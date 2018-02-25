<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 24.02.18
 * Time: 21:21
 */
namespace KotaShade\Yii2StateMachine\models;

use yii\base\Component;
use yii\base\Model;
use KotaShade\yii2\models\TransitionAInterface;

class StateMachine extends Model
{
    protected $actionRepository;

    public function doAction($objE, $action, array $data = [])
    {
        if (($transitionE = $this->getActionTransition($objE, $action)) == null) {
            throw new ExceptionNS\ActionNotAllowed('Действие '.$action.' не существует для текущего состояния объекта');
        }
        $conditionName = $transitionE->getCondition();
        if ($condition != null && $this->checkActionCondition($errors, $conditionName, $objE, $data) == false) {
            throw new ExceptionNS\ActionNotAllowed($message);
        }

        $res= [];
        //FIXME выполнить префунктор

        //FIXME выполнить постфунктор
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
        if ($this->checkActionCondition($erors, $conditionName, $objE, $data=[]) == false) {
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

    abstract protected function getTransitionAForState($stateE, $actionE);
    abstract protected function getObjectState($objE);
    abstract protected function setObjectState($objE, $stateE);

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
        $validateData = array_merge(['_objE' => $objE], $data);
        /**
         * FIXME тут нужно спецвалидатор, который бы не зависел применения доктрины или обычной модели
         * в случае доктрины нужно создать аналог validatorChain иначе аналог, который передаст в валидатор модель.
         * нужно еще ключ до первой ошибки или проверять все
         *
         */
        /** @var \yii\validators\Validator $validator */
        foreach ($this->getConditions($conditionName) as $validator) {
            if (($validator->validate($validateData, $error)) == false) {
                $validatorMessages = $error;
                return false;
            }
        }

        return true;
    }

    /**
     * Возвращает
     * @param string $condition
     */
    protected function getConditions($conditionName)
    {
        $validators = [];
        /** @var \yii\validators\Validator $validator */
        foreach ($this->getValidators() as $validator) {
            if ($validator->isActive($conditionName)) {
                $validators[] = $validator;
            }
        }
        return $validators;
    }

    protected function getActionEntity($action)
    {
        $actionE = $this->actionRepository->find($action);
    }

    /**
     * @param array $validatorMessages
     * @return self
     */
    public function setValidatorMessages(array $validatorMessages)
    {
        $this->validatorMessages[] = $validatorMessage;
        return $this;
    }
}