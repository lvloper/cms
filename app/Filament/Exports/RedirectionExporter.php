<?php

namespace App\Filament\Exports;

use App\Models\Redirection;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class RedirectionExporter extends Exporter
{
    protected static ?string $model = Redirection::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('old_url')
                ->label('URL Origen'),
            ExportColumn::make('new_url')
                ->label('URL Destino'),
            ExportColumn::make('redirect_code')
                ->label('Código'),
            ExportColumn::make('is_active')
                ->label('Activa')
                ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),
            ExportColumn::make('description')
                ->label('Descripción'),
            ExportColumn::make('created_at')
                ->label('Creada'),
            ExportColumn::make('updated_at')
                ->label('Actualizada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de redirecciones se completó y se exportaron ' . number_format($export->successful_rows) . ' ' . str('fila')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('fila')->plural($failedRowsCount) . ' no se pudieron exportar.';
        }

        return $body;
    }
}