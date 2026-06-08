<?php

namespace App\Filament\Forms\Components;

use App\Models\Route;
use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Concerns\HasLabel;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\RawJs;
use Illuminate\Contracts\Support\Htmlable;

class RoutePicker extends Component
{
    use HasLabel;

    protected string $view = 'filament.forms.components.route-picker';

    protected string $fieldName;

    protected string $pickerLabel = 'Enlace';

    protected bool $forceExternal = false;

    protected bool $isRequired = false;

    protected ?Closure $routeFilter = null;

    protected bool $allowsExternal = true;

    protected bool $allowsFile = true;

    protected bool $allowsAnchor = true;

    protected bool $hasButtonLabel = false;

    public function __construct(string $name)
    {
        $this->fieldName = $name;
    }

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function pickerLabel(string $label): static
    {
        $this->pickerLabel = $label;

        return $this;
    }

    public function label(string | Htmlable | Closure | null $label): static
    {
        if (is_string($label)) {
            $this->pickerLabel($label);
        }

        $this->label = $label;

        return $this;
    }

    public function forceExternal(bool $condition = true): static
    {
        $this->forceExternal = $condition;

        return $this;
    }

    public function required(bool $condition = true): static
    {
        $this->isRequired = $condition;

        return $this;
    }

    public function routeFilter(?Closure $filter): static
    {
        $this->routeFilter = $filter;

        return $this;
    }

    public function allowExternal(bool $condition = true): static
    {
        $this->allowsExternal = $condition;

        return $this;
    }

    public function allowFile(bool $condition = true): static
    {
        $this->allowsFile = $condition;

        return $this;
    }

    public function allowAnchor(bool $condition = true): static
    {
        $this->allowsAnchor = $condition;

        return $this;
    }

    public function buttonLabel(bool $condition = true): static
    {
        $this->hasButtonLabel = $condition;

        return $this;
    }

    /**
     * @deprecated Use buttonLabel().
     */
    public function btnLabel(bool $condition = true): static
    {
        return $this->buttonLabel($condition);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->schema(fn (): array => $this->getRoutePickerSchema());
        $this->columns(fn (): int => $this->allowsAnchor ? 2 : 1);
        $this->extraAttributes([
            'class' => 'rounded-xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-900/40',
        ]);
    }

    protected function getRoutePickerSchema(): array
    {
        if ($this->forceExternal) {
            return $this->getForcedExternalSchema();
        }

        return [
            TextInput::make($this->fieldName . '.btn_label')
                ->label('Etiqueta del botón')
                ->hidden(! $this->hasButtonLabel)
                ->required($this->isRequired),

            Select::make($this->fieldName . '.route_id')
                ->label('Seleccionar ' . $this->pickerLabel)
                ->options(fn (): array => $this->getRouteOptions())
                ->searchable()
                ->required($this->isRequired)
                ->getSearchResultsUsing(fn (string $search): array => $this->getRouteOptions($search))
                ->getOptionLabelUsing(fn ($value): ?string => $this->getRouteOptionLabel($value))
                ->preload()
                ->reactive()
                ->afterStateUpdated(function (Set $set, $state): void {
                    if ((string) $state === '0') {
                        $set($this->fieldName . '.new_window', true);
                    }
                }),

            TextInput::make($this->fieldName . '.external_url')
                ->label('URL externa')
                ->required(fn ($get): bool => (string) $get($this->fieldName . '.route_id') === '0' && $this->isRequired)
                ->url()
                ->visible(fn ($get): bool => (string) $get($this->fieldName . '.route_id') === '0'),

            FileUpload::make($this->fieldName . '.file')
                ->label('Archivo')
                ->helperText('Sube un archivo para enlazarlo como descarga.')
                ->required(fn ($get): bool => (string) $get($this->fieldName . '.route_id') === '-1' && $this->isRequired)
                ->preserveFilenames()
                ->directory('files')
                ->visibility('public')
                ->acceptedFileTypes($this->getAcceptedFileTypes())
                ->visible(fn ($get): bool => $this->allowsFile && (string) $get($this->fieldName . '.route_id') === '-1'),

            TextInput::make($this->fieldName . '.download_name')
                ->label('Nombre de la descarga')
                ->placeholder('Ej: Brochure.pdf')
                ->helperText('Opcional: será el nombre sugerido del archivo al descargar.')
                ->maxLength(255)
                ->visible(fn ($get): bool => $this->allowsFile && (string) $get($this->fieldName . '.route_id') === '-1'),

            TextInput::make($this->fieldName . '.anchor')
                ->label('Ancla')
                ->visible(fn ($get): bool => $this->allowsAnchor
                    && (string) $get($this->fieldName . '.route_id')
                    && (string) $get($this->fieldName . '.route_id') !== '0'
                    && (string) $get($this->fieldName . '.route_id') !== '-1')
                ->prefix('#')
                ->helperText('Ej: seccion-contacto (sin incluir el #)')
                ->mask(RawJs::make(<<<'JS'
                    function (value) {
                        return value
                        .replace(/ /g, '-')
                        .replace(/[^a-z-\s]+/g, '');
                    }
                JS))
                ->maxLength(100),

            Checkbox::make($this->fieldName . '.new_window')
                ->visible(fn ($get): bool => $this->allowsExternal && (string) $get($this->fieldName . '.route_id') !== '-1')
                ->label('Abrir en nueva ventana')
                ->columnSpan($this->allowsAnchor ? 'full' : 'auto')
                ->default(fn ($get): bool => (string) $get($this->fieldName . '.route_id') === '0'),
        ];
    }

    protected function getForcedExternalSchema(): array
    {
        return [
            TextInput::make($this->fieldName . '.external_url')
                ->label('URL externa')
                ->url()
                ->required($this->isRequired),
            Checkbox::make($this->fieldName . '.new_window')
                ->label('Abrir en nueva ventana')
                ->default(true)
                ->accepted(),
        ];
    }

    protected function getRouteOptions(?string $search = null): array
    {
        $options = Route::getSelectOptions($search, $this->allowsExternal, $this->routeFilter);

        $prefixedOptions = [];

        if ($this->allowsExternal) {
            $prefixedOptions['0'] = 'Enlace externo';
        }

        if ($this->allowsFile) {
            $prefixedOptions['-1'] = 'Subir archivo';
        }

        return $prefixedOptions + $options;
    }

    protected function getRouteOptionLabel($value): ?string
    {
        return match ((string) $value) {
            '0' => 'Enlace externo',
            '-1' => 'Subir archivo',
            default => Route::find($value)?->title,
        };
    }

    protected function getAcceptedFileTypes(): array
    {
        return [
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
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
            'image/gif',
        ];
    }
}
