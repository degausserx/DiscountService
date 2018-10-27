<?php

namespace App\Repositories;

use App\Contracts\Repositories\JsonRepositoryContract;
use Countable;
use Storage;

abstract class JsonRepository implements JsonRepositoryContract, Countable {

    protected $data = array();

    protected function setSource($source, $callback = null) {
        $data = json_decode(Storage::disk('local')->get($source . '.json'), true);
        if (is_callable($callback)) $this->data = $callback($data);
        else return $data;
    }

    public function count() {
        return count($this->products);
    }
    
    public function getAll() {
        return $this->data;
    }

    public function findById($x) {
        $result = array_filter($this->data, function($data) use ($x) {
            return $data->id == $x;
        });

        if (empty($result)) return null;
        $key = key($result);
        return $result[$key];
    }

}