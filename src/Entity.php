<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 2018/4/24
 */

namespace Koala\Entity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Koala\Entity\Exceptions\InvalidArgumentException;
use Koala\Entity\Exceptions\ValidatorException;

/**
 * 映射entity，需要在entity中定义对应的属性
 * 格式需要符合驼峰模式，否则不能正常映射
 *
 * Class KoalaMapEntity
 * @package Koala\Entity
 */
class Entity extends AbstractEntity
{
    const CREATE = 'create';

    const UPDATE = 'update';

    /**
     * 提交的初始化数据
     * @var array
     */
    protected $originalData = [];

    /**
     * 数据验证规则，原始数据的验证
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * array2object
     * @var array
     */
    protected $mapping = [];

    /**
     * object2array
     * @var array
     */
    protected $reverse = [];


    public function initData(array $data)
    {
        $this->originalData = $data;

        return $this;
    }

    /**
     * 作为更新时，可以支持传入$object
     * @param null|Model $object
     * @return $this
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function hydrate($object = null)
    {
        if (!$this->originalData) {
            throw new InvalidArgumentException('You have to initialize the data first.');
        }
        $data = $this->originalData;
        $reflection = $this->getReflection();
        if ($object instanceof Model) {
            $data = array_merge($object->toArray(), $data);
        }
        if ($this->mapping) {
            $reflection->setMapping($this->mapping);
        }

        return $reflection->hydrate($data, $this);
    }

    /**
     * @return array
     * @throws Exceptions\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function extract()
    {
        $reflection = $this->getReflection();
        if ($this->reverse) {
            // 本来是用array_flip($this->mapping)
            $reflection->setReverse($this->reverse);
        }

        return $reflection->extract($this);
    }


    /**
     * todo 验证规则的特殊需求
     * 数据验证
     * @param string $validationType 验证规则类型（create, update）
     * @return $this
     * @throws InvalidArgumentException
     */
    public function validate($validationType = '')
    {
        if (!$this->originalData) {
            throw new InvalidArgumentException('You have to initialize the data first.');
        }
        $data = $this->originalData;
        $rules = Arr::get($this->rules, $validationType);
        if ($rules && is_array($rules)) {
            $validator = Validator::make($data, $rules, $this->messages, $this->attributes);
            if ($validator->fails()) {
                throw new ValidatorException($validator->errors());
            }
        }

        return $this;
    }

    /**
     * @return Reflection
     */
    public function getReflection()
    {
        return app(Reflection::class);
    }

}