<?php

namespace App\Models\Traits;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

trait HasThumb
{
    public $thumbPath;
    public array $thumbSize = [250, 250];


    public function getThumbAttribute()
    {
        // return Storage::url($this->image);

        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

    // Para archivos guardados en el disco 'public', devolver URL pública (/storage/...)
    return Storage::url($this->image);

        // $thumbName = pathinfo($this->image, PATHINFO_FILENAME) . '.webp';
        // $this->thumbPath = Storage::url('public/thumbs/' . $thumbName);

        // if (!$this->existThumb()) {
        //     $this->thumbPath = $this->createThumb();
        // }


        // return $this->thumbPath;
    }

    public function setThumbSize(array $size)
    {
        $this->thumbSize = $size;
    }

    public function getThumb($size = 'default')
    {
        // Por ahora, simplemente retornar el thumb attribute
        // Puedes expandir esto para manejar diferentes tamaños
        return $this->thumb;
    }

    private function existThumb()
    {
        $path = Storage::path('public/thumbs/' . pathinfo($this->image, PATHINFO_FILENAME) . '.webp');
        return file_exists($path);
    }

    private function createThumb()
    {
        $path = Storage::path('public/' . $this->image);


        if (!file_exists($path)) {
            return false;
        }

        return Image::make($path)
            ->fit($this->thumbSize[0], $this->thumbSize[1])
            ->encode('webp', 80)
            ->save($this->thumbPath);
    }
}
