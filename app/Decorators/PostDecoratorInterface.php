<?php
// app/Decorators/PostDecoratorInterface.php

namespace App\Decorators;

interface PostDecoratorInterface
{
    /**
     * Process post data and return processed array
     *
     * @return array
     */
    public function process(): array;
}
