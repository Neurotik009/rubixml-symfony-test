<?php

namespace App\Interfaces;

interface MLInterface
{
    public function trainModel();

    public function predict() : array;

    public function validate() : array;

    public function getAccuracy() : float;

    public function getF1() : float;
}
