<?php

namespace App\Controller\Admin\Provider\Interface;

interface
FaqMenuProviderInterface
{
    public function getItems(): iterable;
}
