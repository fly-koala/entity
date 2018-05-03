<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 2018/4/24
 */

namespace Koala\Entity;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Koala\Entity\Exceptions\InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

/**
 * 映射关系处理
 * 将数组转为对应的entity，默认的映射关系是下划线<===>驼峰，可以在entity自定以filedMapping
 * Class Reflection
 * @package Koala\Entity
 */
class Reflection
{

    /**
     * Map for hydrate name conversion.
     *
     * @var array
     */
    protected $mapping = [];

    /**
     * Reversed map for extract name conversion.
     *
     * @var array
     */
    protected $reverse = [];

    /**
     * @var array
     */
    protected static $reflectProperties = [];

    /**
     * @param $object
     * @return array
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function extract($object)
    {
        $result = [];
        foreach (self::getReflectProperties($object) as $property) {
            $value = $property->getValue($object);
            $name = $this->extractName($property->getName());
            $result[$name] = $value;
        }

        return $result;
    }


    /**
     * 转化对应的将object的Property转为对应array的key
     * 默认是下划线结构
     * @param string $name
     * @return string
     */
    public function extractName($name)
    {
        return (string)Arr::get($this->reverse, $name, Str::snake($name));
    }

    /**
     * @param array $data
     * @param $object
     * @return mixed
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function hydrate(array $data, $object)
    {
        $reflectProperties = self::getReflectProperties($object);
        foreach ($data as $key => $value) {
            $name = $this->hydrateName($key);
            if (isset($reflectProperties[$name])) {
                $reflectProperties[$name]->setValue($object, $value);
            }
        }

        return $object;
    }


    /**
     * 转化对应的将array的key转为对应object的Property
     * 默认驼峰结构
     * @param string $key
     * @return string
     */
    public function hydrateName($key)
    {
        return (string)Arr::get($this->mapping, $key, Str::camel($key));
    }


    /**
     * @param array $mapping
     * @return $this
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * @param array $reverse
     * @return $this
     */
    public function setReverse(array $reverse)
    {
        $this->reverse = $reverse;

        return $this;
    }


    /**
     * @param $input
     * @return ReflectionProperty[]
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    protected static function getReflectProperties($input)
    {
        if (is_object($input)) {
            $input = get_class($input);
        } elseif (!is_string($input)) {
            throw new InvalidArgumentException('Input must be a string or an object.');
        }

        if (isset(static::$reflectProperties[$input])) {
            return static::$reflectProperties[$input];
        }

        static::$reflectProperties[$input] = [];
        $reflectClass = new ReflectionClass($input);
        $reflectProperties = $reflectClass->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($reflectProperties as $property) {
            $property->setAccessible(true);
            static::$reflectProperties[$input][$property->getName()] = $property;
        }

        return static::$reflectProperties[$input];
    }

}