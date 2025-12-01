<?php

namespace App\Controller\Admin\Dashboard;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class MainMenuProvider
{
    public function getMainMenu(): iterable
    {
        yield MenuItem::linkToRoute('Go to the chat', 'fa fa-home', 'app_home', ['target' => '_blank']);
    }
}
