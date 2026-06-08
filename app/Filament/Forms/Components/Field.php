<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;

class Field
{
    public static function text(string $name, ?string $label = null): TextInput
    {
        return TextInput::make($name)->label($label);
    }

    public static function textarea(string $name, ?string $label = null): Textarea
    {
        return Textarea::make($name)->label($label);
    }

    public static function rich(string $name, ?string $label = null, string $profile = 'basic'): RichEditor
    {
        return RichEditor::make($name)
            ->label($label)
            ->toolbarButtons(config('admin.richEditor.' . $profile, config('admin.richEditor.basic')));
    }

    public static function repeater(string $name, ?string $label = null, array $schema = []): Repeater
    {
        return Repeater::make($name)
            ->label($label)
            ->schema($schema);
    }

    public static function image(
        string $name,
        string $label = 'Imagen',
        string $width = '640',
        string $height = '480',
        string $directory = 'images',
        bool $forceRatio = false,
    ) {
        return Image::make($name, $label, $width, $height, $directory, forceRatio: $forceRatio);
    }

    public static function video(
        string $videoType = 'videoType',
        string $videoId = 'videoId',
        string $videoFile = 'videoFile',
        string $directory = 'videos',
        bool $required = false,
        string $videoImage = 'videoImage',
        string $imageDirectory = 'images/videos',
    ): array {
        return Video::make($videoType, $videoId, $videoFile, $directory, $required, $videoImage, $imageDirectory);
    }

    public static function route(string $name, string $label = 'Enlace'): RoutePicker
    {
        return RoutePicker::make($name)->label($label);
    }

    public static function toggleButtons(string $name, ?string $label = null, array $options = []): ToggleButtons
    {
        return ToggleButtons::make($name)
            ->label($label)
            ->options($options);
    }
}
