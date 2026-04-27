<?php

return [
    'actions' => [
        'add' => [
            'label' => 'Agregar elemento',
            'modal' => [
                'heading' => 'Agregar elemento',
                'actions' => [
                    'create' => 'Crear',
                ],
            ],
        ],

        'add-child' => [
            'label' => 'Agregar hijo',
            'modal' => [
                'heading' => 'Agregar hijo',
                'actions' => [
                    'create' => 'Crear',
                ],
            ],
        ],

        'edit' => [
            'label' => 'Editar',
            'modal' => [
                'heading' => 'Editar elemento',
                'actions' => [
                    'save' => 'Guardar',
                ],
            ],
        ],

        'delete' => [
            'label' => 'Eliminar',
            'modal' => [
                'heading' => 'Eliminar elemento',
                'actions' => [
                    'confirm' => 'Confirmar',
                ],
            ],
        ],

        'toggle-children' => [
            'label' => 'Alternar hijos',
        ],

        'reorder' => [
            'label' => 'Haz clic y arrastra para reordenar',
        ],

        'indent' => [
            'label' => 'Indentar',
        ],

        'dedent' => [
            'label' => 'Desindentar',
        ],

        'moveUp' => [
            'label' => 'Mover arriba',
        ],

        'moveDown' => [
            'label' => 'Mover abajo',
        ],
    ],

    'items' => [
        'empty' => 'Sin elementos.',
        'label' => 'Etiqueta',
        'untitled' => 'Elemento sin título',
    ],
];
