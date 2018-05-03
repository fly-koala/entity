<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 2018/5/2
 */

namespace Koala\Entity;


abstract class AbstractEntity
{

    abstract function initData(array $data);

    abstract function hydrate($object = null);

    abstract function extract();

    abstract function validate($validationType = '');
}