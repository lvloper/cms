<?php

namespace App\Filament\Imports;

use App\Models\Redirection;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class RedirectionImporter extends Importer
{
    protected static ?string $model = Redirection::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('old_url')
                ->label('URL Origen')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
                
            ImportColumn::make('new_url')
                ->label('URL Destino')
                ->rules(['nullable', 'string', 'max:255'])
                ->example('nueva-pagina o https://ejemplo.com'),
                
            ImportColumn::make('redirect_code')
                ->label('Código de Redirección')
                ->numeric()
                ->rules(['integer', 'in:301,302,303,307,308'])
                ->example('301'),
                
            ImportColumn::make('is_active')
                ->label('Activa')
                ->boolean()
                ->rules(['boolean'])
                ->example('true'),
                
            ImportColumn::make('description')
                ->label('Descripción')
                ->rules(['nullable', 'string'])
                ->example('Descripción opcional'),
        ];
    }

    public function resolveRecord(): ?Redirection
    {
        // Buscar duplicados por old_url
        $existing = Redirection::where('old_url', $this->data['old_url'])->first();
        
        if ($existing) {
            // Actualizar el registro existente
            return $existing;
        }
        
        // Crear nuevo registro
        return new Redirection();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de redirecciones se completó y se procesaron ' . number_format($import->successful_rows) . ' ' . str('fila')->plural($import->successful_rows) . '.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('fila')->plural($failedRowsCount) . ' no se pudieron importar.';
        }

        return $body;
    }
}