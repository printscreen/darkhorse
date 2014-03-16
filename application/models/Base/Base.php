<?php
class Model_Base_Base
{
    public function __construct() {}

    /*
    * Returns an array of the object for only protected and public members.
    * This method maybe overwritten by child models
    */
    public function toArray()
    {
        $objArray = array();
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC + ReflectionProperty::IS_PROTECTED);
        foreach($properties as $property) {
            $propertyName = $property->name;
            if(!is_object($this->$propertyName)) {
                if(substr($propertyName,0,1) == '_') {
                    $propertyName = substr($propertyName, 1);
                }
                if(is_array($this->{$property->name})) {
                    $subObject = array();
                    foreach($this->{$property->name} as $subProperty) {
                        if(is_object($subProperty) && method_exists($subProperty, 'toArray')) {
                            $subObject[] = $subProperty->toArray();
                        } else {
                            $subObject[] = $subProperty;
                        }
                    }
                    $objArray[$propertyName] = $subObject;
                } else {
                    $objArray[$propertyName] = $this->{$property->name};
                }
            }
        }
        return $objArray;
    }
}