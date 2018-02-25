<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 25.02.18
 * Time: 16:32
 */

namespace KotaShade\Yii2StateMachine\models;

use Yii;

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
     * репозитарий ентити-таблицы_A переходов
     * @var \Doctrine\ORM\EntityRepository
     */
    private $transitionARepository = null;

    protected function __construct(array $config = [])
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
        /** @var \yii\doctrine\components\DoctrineComponent $doctrineC */
        $doctrineC = Yii::$app->{$entityManagerName};
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

    protected function getObjectState($objE)
    {
        $getStateMethod = 'get' . ucfirst($this->stateAttribute);
        if (method_exists($objE, $getStateMethod) == false) {
            throw new ExceptionNS\InvalidStateGetter('method '
                . $getStateMethod . "doesn't exists in class " . get_class($objE));
        }
        $objE->$getStateMethod($stateE);
        return $objE;
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
            throw new ExceptionNS\InvalidStateGetter('method '
                . $setStateMethod . "doesn't exist in class " . get_class($objE));
        }
        $objE->$setStateMethod($stateE);
        return $objE;
    }

}