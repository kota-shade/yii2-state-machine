<?php
/**
 * Created by PhpStorm.
 * User: kota
 * Date: 26.02.18
 * Time: 17:00
 */

namespace KotaShade\Yii2StateMachine\models;


interface FunctorInterface
{
    /**
     * @param object $objE
     * @param array $data
     * @return mixed
     */
    public function __invoke($objE, array &$data);
}