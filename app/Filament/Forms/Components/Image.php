<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Tabs;

class Image
{
    public static function make(
        string $name,
        string $label = 'Imagen',
        string $width = '640',
        string $height = '480',
        string $directory = 'images',
        bool $hasMobile = false,
        ?string $widthMobile = null,
        ?string $heightMobile = null,
        bool $required = false,
        bool $forceRatio = false,
    ): FileUpload | Tabs {
        $image = self::upload($name, $label, $width, $height, $directory, $forceRatio)
            ->required($required)
            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'])
            ->rules([
                'mimes:jpeg,jpg,png,webp,gif',
                'mimetypes:image/jpeg,image/jpg,image/png,image/webp,image/gif',
            ])
            ->validationMessages([
                'mimes' => 'Por favor, sube una imagen válida. Los formatos permitidos son: JPG, JPEG, PNG, WEBP y GIF.',
                'mimetypes' => 'Por favor, sube una imagen válida. Los formatos permitidos son: JPG, JPEG, PNG, WEBP y GIF. Verifica que tu archivo tenga la extensión correcta.',
            ]);

        if (! $hasMobile) {
            return $image;
        }

        return Tabs::make('image_desktop_mobile')
            ->tabs([
                Tabs\Tab::make('Escritorio')
                    ->schema([$image])
                    ->icon('heroicon-o-computer-desktop'),
                Tabs\Tab::make('Mobile')
                    ->schema([
                        self::upload($name . '_mobile', $label, $widthMobile, $heightMobile, $directory, $forceRatio)
                            ->helperText('Esta en dispositivos móviles, en caso de no asignar, se mostrará la imagen de escritorio'),
                    ])
                    ->icon('heroicon-o-device-phone-mobile'),
            ])
            ->columns(2);
    }

    protected static function upload(
        string $name,
        string $label,
        ?string $width,
        ?string $height,
        string $directory,
        bool $forceRatio = false,
    ): FileUpload {
        $upload = FileUpload::make($name)
            ->label($label)
            ->image()
            ->imageEditor()
            ->imageEditorMode(1)
            ->orientImagesFromExif()
            ->multiple(false)
            ->directory($directory)
            ->when(
                $width && $height,
                fn ($component) => $component->helperText("Tamaño recomendado: $width x $height")
            )
            ->visibility('public');

        if ($forceRatio && $width && $height) {
            $gcd = self::gcd((int) $width, (int) $height);
            $ratioW = (int) $width / $gcd;
            $ratioH = (int) $height / $gcd;
            $ratio = "{$ratioW}:{$ratioH}";

            $upload->imageAspectRatio($ratio)
                ->imageEditorAspectRatioOptions([$ratio])
                ->automaticallyOpenImageEditorForAspectRatio();
        }

        if (! $forceRatio && $width && $height) {
            $upload->imageResizeTargetWidth($width)
                ->imageResizeTargetHeight($height)
                ->imageResizeMode('cover')
                ->imageResizeUpscale(true)
                ->preserveFilenames();
        }

        return $upload;
    }

    protected static function gcd(int $a, int $b): int
    {
        return $b === 0 ? $a : self::gcd($b, $a % $b);
    }
}
