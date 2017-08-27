<?php

namespace AppBundle\Test;

use Symfony\Component\Form\Test\TypeTestCase as BaseTypeTestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class TypeTestCase extends BaseTypeTestCase
{
    public function fromArray($object, array $formData)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($formData as $key => $data) {
            $propertyAccessor->setValue($object, $key, $data);
        }

        return $object;
    }

    /**
     * @param $classObj
     * @param $method
     * @param $params
     *
     * @return mixed
     */
    public function getResultFromMethod($classObj, $method, $params = [])
    {
        $reflectionClass = new \ReflectionClass(get_class($classObj));
        $method = $reflectionClass->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($classObj, $params);
    }

    /**
     * @param $classObj
     * @param $property
     *
     * @return mixed
     */
    public function getProperty($classObj, $property)
    {
        $reflectionClass = new \ReflectionClass(get_class($classObj));
        $property = $reflectionClass->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($classObj);
    }


    /**
     * @param $classObj
     * @param $property
     * @param $value
     */
    public function setProperty($classObj, $property, $value)
    {
        $reflectionClass = new \ReflectionClass(get_class($classObj));
        $property = $reflectionClass->getProperty($property);
        $property->setAccessible(true);

        return $property->setValue($classObj, $value);
    }
}