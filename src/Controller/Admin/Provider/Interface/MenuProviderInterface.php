<?php

namespace App\Controller\Admin\Provider\Interface;

interface MenuProviderInterface
{
    public function getItems(): iterable;
}
