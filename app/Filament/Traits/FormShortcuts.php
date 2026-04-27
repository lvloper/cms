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
        \Closure $filter = null,
        bool $allowExternal = true,
        bool $allowAnchor = true,
        bool $btnLabel = false
    ): \Filament\Forms\Components\Group {

        $options = \App\Models\Route::getSelectOptions(null, $allowExternal, $filter);
        // Ensure "Enlace externo" and "Subir archivo" appear first
        $prefixedOptions = [];
        if ($allowExternal) {
            $prefixedOptions['0'] = 'Enlace externo';
            $prefixedOptions['-1'] = 'Subir archivo';
        }
        // Prepend while preserving insertion order and avoiding duplicate keys from provider
        $options = $prefixedOptions + $options;




        if ($forceExternal) {
            return \Filament\Forms\Components\Group::make([
                \Filament\Forms\Components\TextInput::make($name . '.external_url')
                    ->label('URL externa')
                    ->url(),
                \Filament\Forms\Components\Checkbox::make($name . '.new_window')
                    ->label('Abrir en nueva ventana')
                    ->default(true),
            ])
                ->extraAttributes([
                    'class' => 'bg-dark-500'
                ]);
        }

        return \Filament\Forms\Components\Group::make([
            \Filament\Forms\Components\TextInput::make($name . '.btn_label')
                ->label('Etiqueta del botón')
                ->hidden(!$btnLabel)
                ->required($required),

            \Filament\Forms\Components\Select::make($name . '.route_id')
                ->label('Seleccionar ' . $label)
                ->options($options)
                ->searchable()
                ->required($required)
                ->getSearchResultsUsing(fn(string $search): array => \App\Models\Route::getSelectOptions($search, $allowExternal, $filter))
                ->getOptionLabelUsing(function ($value) {
                    if ($value === '0') return '🌐 Enlace externo';
                    if ($value === '-1') return '📎 Subir archivo';
                    return \App\Models\Route::find($value)?->name;
                })
                ->preload()
                ->reactive()
                ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) use ($name) {
                    if ((string)$state === '0') {
                        $set($name . '.new_window', true);
                    }
                }),

            \Filament\Forms\Components\TextInput::make($name . '.external_url')
                ->label('URL externa')
                ->required(fn(\Filament\Forms\Get $get) => $get($name . '.route_id') === '0' && $required)
                ->url()
                ->visible(fn(\Filament\Forms\Get $get) => $get($name . '.route_id') === '0'),

            // File upload option when selecting "Subir archivo"
            \Filament\Forms\Components\FileUpload::make($name . '.file')
                ->label('Archivo')
                ->helperText('Sube un archivo para enlazarlo como descarga.')
                ->required(fn(\Filament\Forms\Get $get) => $get($name . '.route_id') === '-1' && $required)
                ->preserveFilenames()
                ->directory('files')
                ->visibility('public')
                ->acceptedFileTypes([
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'text/plain',
                    'application/zip',
                    'application/x-zip-compressed',
                    // common image types just in case
                    'image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif',
                ])
                ->visible(fn(\Filament\Forms\Get $get) => $get($name . '.route_id') === '-1'),

            // Download name when using file upload
            \Filament\Forms\Components\TextInput::make($name . '.download_name')
                ->label('Nombre de la descarga')
                ->placeholder('Ej: Brochure.pdf')
                ->helperText('Opcional: será el nombre sugerido del archivo al descargar.')
                ->maxLength(255)
                ->visible(fn(\Filament\Forms\Get $get) => $get($name . '.route_id') === '-1'),

            \Filament\Forms\Components\TextInput::make($name . '.anchor')
                ->label('Ancla')
                ->visible(fn(\Filament\Forms\Get $get) => 
                    $allowAnchor && 
                    $get($name . '.route_id') && 
                    $get($name . '.route_id') !== '0' && 
                    $get($name . '.route_id') !== '-1'
                )
                ->prefix('#')
                ->helperText('Ej: seccion-contacto (sin incluir el #)')
                ->mask(\Filament\Support\RawJs::make(<<<'JS'
                    function (value) {
                        return value
                        .replace(/ /g, '-')
                        .replace(/[^a-z-\s]+/g, '');
                    }
                JS))
                ->maxLength(100),

            \Filament\Forms\Components\Checkbox::make($name . '.new_window')
                ->visible(fn(\Filament\Forms\Get $get) => $allowExternal && $get($name . '.route_id') !== '-1')
                ->label('Abrir en nueva ventana')
                ->columnSpan($allowAnchor ? 'full' : 'auto')
                ->default(fn(\Filament\Forms\Get $get) => (string)$get($name . '.route_id') === '0')
                ->accepted($forceExternal),



        ])
            ->columns($allowAnchor ? 2 : 1)
            ->extraAttributes([
                'class' => 'rounded-md p-4',
                'style' => 'background-color: rgba(0,0,0,.1);'
            ]);
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
    ): FileUpload | \Filament\Forms\Components\Tabs {
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
            $tab = \Filament\Forms\Components\Tabs::make('image_desktop_mobile')
                ->tabs([
                    \Filament\Forms\Components\Tabs\Tab::make('Escritorio')
                        ->schema([$image])
                        ->icon('heroicon-o-computer-desktop'),
                    \Filament\Forms\Components\Tabs\Tab::make('Mobile')
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

    // public static function PersonPicker($name, $label = 'Datos de la persona') : \Filament\Forms\Components\Fieldset {
    //     return \Filament\Forms\Components\Fieldset::make($label)
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
