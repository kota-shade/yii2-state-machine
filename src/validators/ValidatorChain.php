<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 02.03.18
 * Time: 11:19
 */

namespace KotaShade\Yii2StateMachine\validators;

use Yii;
use KotaShade\Yii2StateMachine\exceptions as ExceptionNS;

/**
 * Validate throw all validators in chain
 * Class ValidatorChain
 * @package KotaShade\Yii2StateMachine\validators
 */
class ValidatorChain extends Validator
{
    /**
     * Default priority at which validators are added
     */
    const DEFAULT_PRIORITY = 1;

    protected $validatorSpec = [];

    /**
     * Validator chain
     *
     * @var \SplPriorityQueue
     */
    protected $validators;

    /**
     * @param object $obj
     * @param array $data
     * @return bool
     */
    public function validate($obj, array $data = [])
    {
        $this->messages = [];
        $result         = true;
        foreach ($this->validators as $element) {
            /** @var ValidatorInterface $validator */
            $validator = $element['instance'];
            if ($validator->validate($obj, $data)) {
                continue;
            }
            $result         = false;
            $messages       = $validator->getMessages();
            $this->messages = array_replace_recursive($this->messages, $messages);
            if ($element['breakChainOnFailure']) {
                break;
            }
        }
        return $result;
    }

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->validators = new \SplPriorityQueue();
        foreach ($this->validatorSpec as $name => $options) {
            $this->attachByName($name, $options, true);
        }
    }

    /**
     * Use the plugin manager to add a validator by name
     *
     * @param  string $name
     * @param  array $options
     * @param  bool $breakChainOnFailure
     * @param  int $priority
     * @return ValidatorChain
     * @throws ExceptionNS\InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    public function attachByName($name, $options = [], $breakChainOnFailure = false, $priority = self::DEFAULT_PRIORITY)
    {
        if (isset($options['break_chain_on_failure'])) {
            $breakChainOnFailure = (bool) $options['break_chain_on_failure'];
            unset($options['break_chain_on_failure']);
        }

        if (isset($options['breakchainonfailure'])) {
            $breakChainOnFailure = (bool) $options['breakchainonfailure'];
            unset ($options['breakchainonfailure']);
        }
        if (is_numeric($name)) {
            $class = $options['class'];
            $options['class'] = Yii::getAlias($class);
            $validator = Yii::createObject($options);
        } else {
            $name = Yii::getAlias($name);
            /**
             * @var ValidatorInterface $validator
             * NB!!! createOptions вторым параметром принимает массив аргументов, которые будут переданы в конструктор
             * в порядке их следования в массиве.
             * у нас единственный аргумент - конфигурация в виде массива опций
             */
            $validator = Yii::createObject($name, [$options]);
        }

        $this->attach($validator, $breakChainOnFailure, $priority);

        return $this;
    }

    /**
     * Attach a validator to the end of the chain
     *
     * If $breakChainOnFailure is true, then if the validator fails, the next validator in the chain,
     * if one exists, will not be executed.
     *
     * @param  ValidatorInterface $validator
     * @param  bool               $breakChainOnFailure
     * @param  int                $priority            Priority at which to enqueue validator; defaults to
     *
     * @throws ExceptionNS\InvalidArgumentException
     *
     * @return self
     */
    public function attach(
        ValidatorInterface $validator,
        $breakChainOnFailure = false,
        $priority = self::DEFAULT_PRIORITY
    ) {
        $this->validators->insert(
            [
                'instance'            => $validator,
                'breakChainOnFailure' => (bool) $breakChainOnFailure,
            ],
            $priority
        );

        return $this;
    }
}