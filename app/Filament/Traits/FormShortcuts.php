<?php

namespace App\Filament\Traits;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\HtmlString;


use Filament\Forms\Components\Textarea;


trait FormShortcuts
{
    use HandlesExternalImages;
    public function getRichEditorToolbarButtons()
    {
        return config('admin.richEditor.basic');
    }

    public static function Input($name): TextInput
    {
        return TextInput::make($name)
            ->maxLength(255);
    }

    public static function TextArea($name): Textarea
    {
        return Textarea::make($name)
            ->maxLength(255);
    }


    /*
    !usar para editor de contenido
    Example:
    
    FormShortcuts::Rich(
        name: 'content', 
        type: 'basic'
    )
    */

    public static function Rich($name, $type = 'basic'): RichEditor
    {
        $richEditor = RichEditor::make($name)
            ->toolbarButtons(config('admin.richEditor.' . $type));

        return $richEditor;
    }


    /*
    !usar para iconos
    Example:
    
    FormShortcuts::IconPicker(
        name: 'icon'
    )
    */
    public static function IconPicker($name = 'icon'): \Guava\IconPicker\Forms\Components\IconPicker
    {
        return \Guava\IconPicker\Forms\Components\IconPicker::make($name)
            ->sets(['lucide']);
    }

    public static function IconSocial($name): \Guava\IconPicker\Forms\Components\IconPicker
    {
        return \Guava\IconPicker\Forms\Components\IconPicker::make($name)
            ->sets(['fontawesome-brands', 'fontawesome-solid']);
    }

   
    public static function RoutePicker(
        string $name,
        string $label = 'Enlace',
        bool $forceExternal = false,
        bool $required = false,
        ?\Closure $filter = null,
        bool $allowExternal = true,
        bool $allowAnchor = true,
        bool $btnLabel = false
    ): \App\Filament\Forms\Components\RoutePicker {
        return \App\Filament\Forms\Components\RoutePicker::make($name)
            ->pickerLabel($label)
            ->forceExternal($forceExternal)
            ->required($required)
            ->routeFilter($filter)
            ->allowExternal($allowExternal)
            ->allowFile($allowExternal)
            ->allowAnchor($allowAnchor)
            ->buttonLabel($btnLabel);
    }



    /*

    !Para imagenes de contenido
    Example:
    
    FormShortcuts::Image(
        name: 'image', 
        label: 'Imagen', 
        width: '640', 
        height: '480'
    )

    Si se necesita una imagen para mobile, se debe pasar el parametro $hasMobile: true
    guarda la imagen en {$name}_mobile
        $hasMobile: true
        $widthMobile: ancho de la imagen para mobile
        $heightMobile: alto de la imagen para mobile


    $directory: directorio de almacenamiento (en general dejar default)

    ! en la vista usar:
            <x-image :image="$image" :imageMobile="$image_mobile" />
            obviamente mobile es opcional
            tambien podes pasarle alt=".." y 
            class=".." (afecta al contenedor)
            imageClass=".." (afecta a la imagen)

    */
    public static function Image(
        $name,
        $label = 'Imagen',
        $width = '640',
        $height = '480',
        $directory = 'images',
        $hasMobile = false,
        $widthMobile = null,
        $heightMobile = null,
        $required = false
    ): FileUpload | \Filament\Schemas\Components\Tabs {
        $image = FileUpload::make($name)
            ->label($label)
            ->image()
            ->required($required)
            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'])
            ->rules([
                'mimes:jpeg,jpg,png,webp,gif',
                'mimetypes:image/jpeg,image/jpg,image/png,image/webp,image/gif'
            ])
            ->validationMessages([
                'mimes' => 'Por favor, sube una imagen válida. Los formatos permitidos son: JPG, JPEG, PNG, WEBP y GIF.',
                'mimetypes' => 'Por favor, sube una imagen válida. Los formatos permitidos son: JPG, JPEG, PNG, WEBP y GIF. Verifica que tu archivo tenga la extensión correcta.'
            ])
            ->imageEditor()
            ->imageEditorMode(2)
            ->preserveFilenames()
            ->imageResizeTargetWidth($width)
            ->imageResizeTargetHeight($height)
            ->imageResizeMode('cover')
            ->imageResizeUpscale(false)
            ->orientImagesFromExif()
            ->multiple(false)
            ->directory($directory)
            ->when(
                $width && $height,
                fn($component) =>
                $component->helperText("Tamaño recomendado: $width x $height")
            )
            ->visibility('public');

        if ($hasMobile) {
            $tab = \Filament\Schemas\Components\Tabs::make('image_desktop_mobile')
                ->tabs([
                    \Filament\Schemas\Components\Tabs\Tab::make('Escritorio')
                        ->schema([$image])
                        ->icon('heroicon-o-computer-desktop'),
                    \Filament\Schemas\Components\Tabs\Tab::make('Mobile')
                        ->schema([
                            FileUpload::make($name . '_mobile')
                                ->label($label)
                                                    ->image()
                                ->imageEditor()
                                ->imageEditorMode(2)
                                ->preserveFilenames()
                                ->helperText('Esta en dispositivos móviles, en caso de no asignar, se mostrará la imagen de escritorio')
                                ->imageResizeTargetWidth($widthMobile)
                                ->imageResizeTargetHeight($heightMobile)
                                ->imageResizeMode('cover')
                                ->imageResizeUpscale(false)
                                ->multiple(false)
                                ->orientImagesFromExif()
                                ->directory($directory)
                                ->visibility('public')
                        ])
                        ->icon('heroicon-o-device-phone-mobile'),
                ])
                ->columns(2);

            return $tab;
        }

        return $image;
    }

    public static function Gallery($name, $label = 'Galería', $directory = 'images', $width = '1280', $height = '640')
    {
        return  FileUpload::make($name)
            ->label($label)
            ->image()
            ->imageEditor()
            ->imageEditorMode(2)
            ->preserveFilenames()
            ->imageResizeTargetWidth($width)
            ->imageResizeTargetHeight($height)
            ->imageResizeMode('cover')
            ->imageResizeUpscale(false)
            ->orientImagesFromExif()
            ->multiple(true)
            ->panelLayout('grid')
            ->reorderable()
            ->directory($directory)
            ->helperText("Tamaño recomendado: $width x $height")
            ->visibility('public');
    }

    public static function TipTap($name, $label, $profile = 'minimal', $required = false): RichEditor
    {
        return RichEditor::make($name)
            ->label($label)
            ->toolbarButtons(config('admin.richEditor.' . $profile, config('admin.richEditor.basic')))
            ->required($required);
    }

    // public static function PersonPicker($name, $label = 'Datos de la persona') : \Filament\Schemas\Components\Fieldset {
    //     return \Filament\Schemas\Components\Fieldset::make($label)
    //         ->schema([
    //             \Filament\Forms\Components\TextInput::make($name . '.name')
    //                 ->label('Nombre')
    //                 ->required(),
    //             \Filament\Forms\Components\TextInput::make($name . '.position')
    //                 ->label('Cargo')
    //                 ->required(),
    //             \Filament\Forms\Components\Textarea::make($name . '.description')
    //                 ->label('Descripción')
    //                 ->rows(3)
    //                 ->required(),
    //             static::Image($name . '.image', 'Foto', 'people', 400, 400)
    //                 ->required()
    //         ])
    //         ->columns(1);
    // }
}
