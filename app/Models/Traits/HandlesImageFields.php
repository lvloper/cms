<?php

namespace App\Models\Traits;

trait HandlesImageFields
{
    /**
     * Define image field names that should be protected against array values.
     * Override this property in your model to customize the list.
     */
    protected array $imageFields = ['image', 'image_mobile', 'photo', 'picture', 'avatar'];

    /**
     * Boot the trait and register mutators for image fields.
     */
    protected static function bootHandlesImageFields(): void
    {
        static::creating(function ($model) {
            $model->normalizeImageFields();
        });

        static::updating(function ($model) {
            $model->normalizeImageFields();
        });
    }

    /**
     * Normalize all image fields to ensure they're strings, not arrays.
     */
    protected function normalizeImageFields(): void
    {
        foreach ($this->getImageFields() as $field) {
            if (isset($this->attributes[$field]) && is_array($this->attributes[$field])) {
                $this->attributes[$field] = $this->attributes[$field][0] ?? null;
            }
        }
    }

    /**
     * Get the list of image fields for this model.
     */
    protected function getImageFields(): array
    {
        return $this->imageFields;
    }

    /**
     * Generic setter for image fields to ensure they're always strings.
     */
    public function setImageAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['image'] = $value[0] ?? null;
        } else {
            $this->attributes['image'] = $value;
        }
    }

    /**
     * Generic setter for image_mobile fields.
     */
    public function setImageMobileAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['image_mobile'] = $value[0] ?? null;
        } else {
            $this->attributes['image_mobile'] = $value;
        }
    }

    /**
     * Generic setter for photo fields.
     */
    public function setPhotoAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['photo'] = $value[0] ?? null;
        } else {
            $this->attributes['photo'] = $value;
        }
    }

    /**
     * Generic setter for picture fields.
     */
    public function setPictureAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['picture'] = $value[0] ?? null;
        } else {
            $this->attributes['picture'] = $value;
        }
    }

    /**
     * Generic setter for avatar fields.
     */
    public function setAvatarAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['avatar'] = $value[0] ?? null;
        } else {
            $this->attributes['avatar'] = $value;
        }
    }
}
