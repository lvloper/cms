<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Filament\Navigation\MenuItem;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;

use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
  public function boot(): void
  {
    app()->setLocale('es');
  }

  public function panel(Panel $panel): Panel
  {
    return $panel
      ->default()
      ->id('admin')
      ->path('admin')
      ->login()
      ->colors([
        'primary' => Color::Emerald,
        'secondary' => Color::Red,
      ])
      ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
      ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
      ->pages([
        Pages\Dashboard::class,
      ])
      ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
      ->widgets([
        \App\Filament\Widgets\BySocies::class,
        Widgets\AccountWidget::class,
      ])
      ->middleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        AuthenticateSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        SubstituteBindings::class,
        DisableBladeIconComponents::class,
        DispatchServingFilamentEvent::class,
      ])
      ->authMiddleware([
        Authenticate::class,
      ])
      // ->renderHook('panels::body.end', fn (): string => Blade::render("@vite('resources/js/app.js')"))
      ->plugins(
        [
          \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
          FilamentEditProfilePlugin::make()
            ->slug('edit-profile') //Set Manual
            ->shouldRegisterNavigation(false)
            ->shouldShowAvatarForm(false),
          // FilamentLaravelLogPlugin::make()
          //   ->navigationGroup('Settings')
          //   ->navigationLabel('Logs')
          //   ->navigationIcon('heroicon-o-bug-ant')
          //   ->navigationSort(1)
          //   ->slug('logs')
        ]
      )
      ->userMenuItems([
        'profile' => MenuItem::make()
          ->label(fn() => auth()->user()->name)
          ->url(fn(): string => EditProfilePage::getUrl())
          ->icon('heroicon-m-user-circle')
          //If you are using tenancy need to check with the visible method where ->company() is the relation between the user and tenancy model as you called
          ->visible(function (): bool {
            return auth()->user()->exists();
          }),
      ])

        ->databaseNotifications()
        ->databaseNotificationsPolling('5s')
      ->brandLogo(fn() => new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 601 101">
  <g id="Grupo_73" data-name="Grupo 73" transform="translate(0.5 0.5)">
    <circle id="Elipse_27" data-name="Elipse 27" cx="50" cy="50" r="50" transform="translate(200)" fill="#ffc700" stroke="#707070" stroke-width="1"/>
    <circle id="Elipse_28" data-name="Elipse 28" cx="50" cy="50" r="50" transform="translate(300)" fill="#ff4d61" stroke="#707070" stroke-width="1"/>
    <circle id="Elipse_29" data-name="Elipse 29" cx="50" cy="50" r="50" transform="translate(400)" fill="#951b81" stroke="#707070" stroke-width="1"/>
    <circle id="Elipse_30" data-name="Elipse 30" cx="50" cy="50" r="50" transform="translate(500)" fill="#0dd6cc" stroke="#707070" stroke-width="1"/>
    <circle id="Elipse_31" data-name="Elipse 31" cx="50" cy="50" r="50" fill="#00f081" stroke="#707070" stroke-width="1"/>
    <g id="Grupo_62" data-name="Grupo 62" transform="translate(33.759 30.756)">
      <g id="Grupo_61" data-name="Grupo 61">
        <path id="Trazado_42" data-name="Trazado 42" d="M273.759,1424.029l5.741-6.858a19.168,19.168,0,0,0,12.068,4.465c2.765,0,4.253-.956,4.253-2.551v-.107c0-1.541-1.223-2.392-6.273-3.561-7.921-1.808-14.035-4.041-14.035-11.7v-.106c0-6.911,5.476-11.908,14.407-11.908,6.326,0,11.27,1.7,15.311,4.944l-5.157,7.283a18.659,18.659,0,0,0-10.42-3.668c-2.5,0-3.721,1.063-3.721,2.392v.106c0,1.7,1.276,2.446,6.432,3.615,8.56,1.861,13.876,4.625,13.876,11.589v.107c0,7.6-6.008,12.121-15.045,12.121A26,26,0,0,1,273.759,1424.029Z" transform="translate(-273.759 -1391.707)" fill="#fff"/>
      </g>
    </g>
    <circle id="Elipse_32" data-name="Elipse 32" cx="50" cy="50" r="50" transform="translate(100)" fill="#1d71ff" stroke="#707070" stroke-width="1"/>
    <g id="Grupo_64" data-name="Grupo 64" transform="translate(129.958 30.649)">
      <g id="Grupo_63" data-name="Grupo 63">
        <path id="Trazado_43" data-name="Trazado 43" d="M369.958,1411.057v-.106c0-10.685,8.612-19.351,20.1-19.351s19.989,8.559,19.989,19.245v.106c0,10.685-8.612,19.351-20.1,19.351S369.958,1421.743,369.958,1411.057Zm29.558,0v-.106c0-5.369-3.881-10.047-9.569-10.047-5.635,0-9.41,4.571-9.41,9.941v.106c0,5.369,3.881,10.047,9.516,10.047C395.741,1421,399.516,1416.427,399.516,1411.057Z" transform="translate(-369.958 -1391.6)" fill="#fff"/>
      </g>
    </g>
    <g id="Grupo_66" data-name="Grupo 66" transform="translate(229.914 30.649)">
      <g id="Grupo_65" data-name="Grupo 65">
        <path id="Trazado_44" data-name="Trazado 44" d="M469.914,1411.057v-.106c0-10.845,8.347-19.351,19.617-19.351,7.6,0,12.493,3.19,15.789,7.762l-7.762,6.007c-2.126-2.658-4.572-4.359-8.133-4.359-5.21,0-8.879,4.412-8.879,9.835v.106c0,5.582,3.669,9.941,8.879,9.941,3.88,0,6.166-1.807,8.4-4.519l7.762,5.529c-3.509,4.838-8.24,8.4-16.48,8.4A18.9,18.9,0,0,1,469.914,1411.057Z" transform="translate(-469.914 -1391.6)" fill="#fff"/>
      </g>
    </g>
    <g id="Grupo_68" data-name="Grupo 68" transform="translate(344.817 31.393)">
      <g id="Grupo_67" data-name="Grupo 67">
        <path id="Trazado_45" data-name="Trazado 45" d="M584.817,1392.344h10.366v37.214H584.817Z" transform="translate(-584.817 -1392.344)" fill="#fff"/>
      </g>
    </g>
    <g id="Grupo_70" data-name="Grupo 70" transform="translate(434.902 31.393)">
      <g id="Grupo_69" data-name="Grupo 69">
        <path id="Trazado_46" data-name="Trazado 46" d="M674.9,1392.344h29.93v8.772H685.109v5.635h17.862v8.134H685.109v5.9H705.1v8.772H674.9Z" transform="translate(-674.902 -1392.344)" fill="#fff"/>
      </g>
    </g>
    <g id="Grupo_72" data-name="Grupo 72" transform="translate(533.759 30.756)">
      <g id="Grupo_71" data-name="Grupo 71">
        <path id="Trazado_47" data-name="Trazado 47" d="M773.759,1424.029l5.741-6.858a19.168,19.168,0,0,0,12.068,4.465c2.765,0,4.253-.956,4.253-2.551v-.107c0-1.541-1.223-2.392-6.273-3.561-7.921-1.808-14.035-4.041-14.035-11.7v-.106c0-6.911,5.476-11.908,14.407-11.908,6.326,0,11.27,1.7,15.311,4.944l-5.157,7.283a18.658,18.658,0,0,0-10.42-3.668c-2.5,0-3.721,1.063-3.721,2.392v.106c0,1.7,1.276,2.446,6.432,3.615,8.56,1.861,13.876,4.625,13.876,11.589v.107c0,7.6-6.008,12.121-15.045,12.121A26,26,0,0,1,773.759,1424.029Z" transform="translate(-773.759 -1391.707)" fill="#fff"/>
      </g>
    </g>
  </g>
</svg>

'))
      ->brandLogoHeight('1.625rem')
    ;
  }
}
