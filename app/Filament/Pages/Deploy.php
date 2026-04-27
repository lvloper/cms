<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Deploy extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationLabel = 'Deploy';
    protected static ?string $title = 'Sistema de Deploy';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.deploy';

    protected static ?string $navigationGroup = 'Configuración';

    public string $output = '';
    public function mount()
    {
        if (Auth::id() !== 1) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function deploy()
    {
        ob_start();
        $exitCode = Artisan::call('deploy');
        $this->output = ob_get_clean() . "\n" . Artisan::output();

        if ($exitCode === 0) {
            Notification::make()
                ->title('Deployment completed successfully')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Deployment failed')
                ->danger()
                ->send();
        }
    }
}
