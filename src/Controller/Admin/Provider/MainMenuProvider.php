<?php

namespace App\Controller\Admin\Provider;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

class MainMenuProvider
{
    public function getMainMenu(): iterable
    {
        yield MenuItem::section('Web Chat');
        // yield MenuItem::linkToRoute('Go to the chat', 'fa fa-home', 'app_home', ['target' => '_blank']);
    }
}
