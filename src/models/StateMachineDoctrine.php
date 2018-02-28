<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 25.02.18
 * Time: 16:32
 */

namespace KotaShade\Yii2StateMachine\models;

use Doctrine\Common\Collections\ArrayCollection;
use Yii;
use KotaShade\Yii2StateMachine\exceptions as ExceptionNS;

class StateMachineDoctrine extends StateMachine
{
    /**
     * @var string
     */
    protected $entityManagerName = 'doctrine';
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em = null;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $actionRepository;

    /**
     * имя словаря-ентити действий
     * @var string
     */
    protected $actionDictionary = null;

    /**
     * репозитарий ентити-таблицы_A переходов
     * @var \Doctrine\ORM\EntityRepository
     */
    private $transitionARepository = null;

    /**
     * имя атрибута, в котором хранится состояние, геттер и сеттер обязательны
     * @var string
     */
    protected $stateAttribute = null;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        /** @var \KotaShade\doctrine\components\DoctrineComponent $doctrineC */
        $doctrineC = Yii::$app->{$this->entityManagerName};
        /** @var \Doctrine\ORM\EntityManager $em */
        $this->em = $doctrineC->getEntityManager();
    }

    /**
     * @param $stateE
     * @param $actionE
     * @return null|TransitionAInterface
     */
    protected function getTransitionAForState($stateE, $actionE)
    {
        $repo = $this->getTransitionARepository();
        $res = $repo->findOneBy([
            'src' => $stateE->getId(),
            'action' => $actionE->getId()
        ]);
        return $res;
    }

    /**
     * возвращает репозитарий таблицы переходов
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getTransitionARepository()
    {
        if ($this->transitionARepository == null) {
            $this->transitionARepository = $this->em->getRepository($this->transitionAClassName);
        }
        return $this->transitionARepository;
    }

    /**
     * @param TransitionAInterface $transitionE
     * @return array|\Traversable
     */
    protected function getTransitionBList(TransitionAInterface $transitionE)
    {
        /** @var ArrayCollection $ret */
        $ret = $transitionE->getTransitionsB();
        return $ret->toArray();
    }

    protected function getObjectState($objE)
    {
        $getStateMethod = 'get' . ucfirst($this->stateAttribute);
        if (method_exists($objE, $getStateMethod) == false) {
            throw new ExceptionNS\InvalidStateMethod('method '
                . $getStateMethod . "doesn't exists in class " . get_class($objE));
        }
        $stateE = $objE->$getStateMethod();
        return $stateE;
    }

    /**
     * устанавливает состояние у объекта
     * @param object $objE
     * @param object $stateE
     * @return object
     */
    protected function setObjectState($objE, $stateE)
    {
        $setStateMethod = 'set' . ucfirst($this->stateAttribute);
        if (method_exists($objE, $setStateMethod) == false) {
            throw new ExceptionNS\InvalidStateMethod('method '
                . $setStateMethod . "doesn't exist in class " . get_class($objE));
        }
        $objE->$setStateMethod($stateE);
        return $objE;
    }

    /**
     * @param $action
     * @return null|object
     */
    protected function getActionEntity($action)
    {
        $actionE = $this->getActionRepository()->find($action);
        return $actionE;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getActionRepository()
    {
        if ($this->actionRepository == null) {
            $this->actionRepository = $this->em->getRepository($this->actionDictionary);
        }
        return $this->actionRepository;
    }
}