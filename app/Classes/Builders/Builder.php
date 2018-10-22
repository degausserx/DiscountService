<?php

namespace App\Builders;

use Exception;
use Ref;

// ended up moving the core pieces into an abstract class
abstract class Builder {

    // value change logger
    protected $logger = array();
    protected $template = array();
    
    // constructor + composition
    protected function __construct(Builder $externalBuilder = null) {
        if ($externalBuilder != null) {
            $this->insert($externalBuilder);
            return $this;
        }
    }


    // a couple magic methods to handle build() having the same name statically and OO
    // build() returns an instance
    final public function __call($prop, $args) {
        if ($prop === 'build') {
            $builder = (isset($args[0])) ? $args[0] : null;
            call_user_func(array($this, 'buildObject'), $builder);
        }
        return $this;
    }

    final public static function __callStatic($prop, $args) {
        if ($prop === 'build') {
            $builder = (isset($args[0])) ? $args[0] : null;
            return static::buildStatic($builder);
        }
    }


    // static instantiation
    final protected static function buildStatic(Builder $externalBuilder = null) {
        if ($externalBuilder != null) {
            return new static($externalBuilder);
        }
        return new static();
    }

    // object builder
    final private function buildObject(Builder $externalBuilder = null) {
        if ($externalBuilder != null) {
            $this->insert($externalBuilder);
        }
    }


    // one sided merge, when copying data from one builder to another
    final private function insert(Builder $externalBuilder) {
        $this->logger = array_merge_recursive($this->logger, $externalBuilder->logger);

        foreach ($this->logger as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
            $this->{$key} = $value;
        }

        return $this;
    }

    public function getData() {
        return $this->logger;
    }


    // add value to property
    protected function add(String $property, $value = null) {
        $this->logger[$property] = $value;

        if (property_exists($this, $property)) {
            $this->{$property} = $value;
        }
    }


    // add a value to the property only if allowed to be set
    protected function addIfSet(String $property, $value = null) {
        if (isset($this->template[$property])) {
            $this->add($property, $value);
        }

        else throw new Exception("The property: {$property} does not exist");
    }


    // add a value to a specific point of the data branch
    protected function addWithItem(String $property, String $item, $value = null) {
        $this->addWithItemWithSet(false, $property, $item, $value);
    }

    // makeing sure the properties exist
    protected function addWithItemIfSet(String $property, String $item, $value = null) {
        if (isset($this->template[$property])) {
            $this->addWithItemWithSet(true, $property, $item, $value);
        }

        else throw new Exception("The property: {$property} does not exist");
    }

    // changing the value based on $property exploded into an array of keys to go down
    private function addWithItemIsSet(Bool $mustBeSet, String $property, String $item, $value = null) {
        $logger = (isset($this->logger[$property])) ? $this->logger[$property] : array();
        $templatePiece = (isset($this->template[$property])) ? $this->template[$property] : array();
        $properties = explode('.', $item);
        $currentProperty = $logger;
        foreach ($properties as $next) {
            if (isset($templatePiece[$next]) || !$mustBeSet) {
                if (isset($currentProperty[$next])) {
                    $currentProperty = $currentProperty[$next];
                }
                else $currentProperty = array();
            }
            else {
                // do nothing? throw exception?
            }
        }
        $currentProperty = $value;
        $this->{$property} = $this->logger[$property];
    }

}