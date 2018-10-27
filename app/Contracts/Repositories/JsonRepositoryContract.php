<?php

namespace App\Contracts\Repositories;

interface JsonRepositoryContract {

    public function count();
    
    public function getAll();

    public function findById($x);

}