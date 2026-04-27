# Filament v5 upgrade notes

Cambios aplicados para compatibilidad del admin:

- Layout/schema components pasan a `Filament\Schemas\Components`: `Tabs`, `Grid`, `Section`, `Group`, `Fieldset`.
- Utilities de closures pasan a `Filament\Schemas\Components\Utilities`: `Get`, `Set`.
- Acciones de recursos/tablas/form fields pasan a `Filament\Actions`: `Action`, `EditAction`, `DeleteAction`, `BulkActionGroup`, `DeleteBulkAction`, etc.
- Tabs de resources usan `Filament\Schemas\Components\Tabs\Tab`.
- Vistas vendor publicadas viejas pueden romper: actualizar overrides con la versión actual de `vendor/`.

Rutas admin crawleadas OK: dashboard, pages, blogs, menus, banners, configurations, redirections, users, roles, edit-profile.
