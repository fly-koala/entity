<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 2018/4/24
 */

namespace Koala;

class Example extends \Koala\Entity\Entity
{
    public $id;

    /**
     * 如果fieldsMapping没有定义映射关系
     * 则对应了数组中的full_name
     * @var string
     */
    public $fullName;

    /**
     * fieldsMapping定义了映射关系
     * @var int
     */
    public $widget;

    /**
     * 自定义映射关系array2object
     * @var array
     */
    protected $mapping = [
        'w' => 'widget'
    ];

    /**
     * 自定义映射关系object2array
     * @var array
     */
    protected $reverse = [
        'widget' => 'w'
    ];


    protected $rules = [
        'create' => [
            'w' => 'int'
        ]
    ];
}

$testObj = new Example();
$data = ['id' => 1, 'full_name' => [1, 2], 'w' => 4];
// 转为对象，映射关系
// first initData
$testObj->initData($data)->validate('create')->hydrate();
print_r($testObj);
// 转为数组 c已经没了
$data = $testObj->extract();
print_r($data);
