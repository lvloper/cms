<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'unique' => 'El campo :attribute ya existe.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'file' => 'El archivo :attribute no debe ser mayor que :max kilobytes.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
        'array' => 'El campo :attribute no debe tener más de :max elementos.',
    ],
    'regex' => 'El campo :attribute no tiene un formato válido.',
    'custom' => [
        'email' => [
            'required' => 'Necesitamos conocer tu dirección de correo electrónico.',
        ],
    ],
];