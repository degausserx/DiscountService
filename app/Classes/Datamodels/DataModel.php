<?php

namespace App\DataModels;

use ArrayAccess;
use Exception;

// generic data class
class DataModel implements ArrayAccess {

    // data 
    protected $data = array();
    protected $required = array();

    // constructor
    protected function __construct(Array $data = array()) {

        foreach ($data as $key => $value) {
            // valid propert can be created
            if ($prop = $this->cleanProp($key)) {
                // property is available to use
                if (property_exists($this, $prop)) {
                    $this->{$prop} = $value;
                }
                // use array container
                else {
                    $this->data[$prop] = $value;
                }

                if (($required = array_search($prop, $this->required)) !== false) {
                    unset($this->required[$required]);
                }
            }
        }

        if (count($this->required) > 0) {
            throw new Exception('Required model properties not supplied: ' . count($this->required));
        }
    }

    // return a group of DataModel instances
    public static function makeGroup(Array $data) {
        $array = array();
        foreach ($data as $item) {
            $array[] = new static($item);
        }
        return $array;
    }

    // return a group of DataModel instances
    public static function make(Array $data) {
        return new static($data);
    }

    // sanatize property names
    private function cleanProp($key) {
        return preg_replace("/[^a-zA-Z0-9_]/", "", $key);
    }

    // all non resolved gets go to $data
    public function __get($key) {
        $prop = $this->cleanProp($key);
        if (isset($this->data[$prop])) {
            return $this->data[$prop];
        }
        return null;
    }

    // all non resolved sets go to $data
    public function __set($key, $value) {
        $prop = $this->cleanProp($key);
        $this->data[$key] = $value;
    }

    // array access
    public function getArrayCopy() {
        return $this->data;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        }
        else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}