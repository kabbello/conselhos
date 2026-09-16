<?php

namespace App\Filament\Painel\Pages;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.painel.login-footer');
    }
}
