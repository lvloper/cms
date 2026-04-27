# Base CMS

A generic CMS base built on Laravel 11 + Filament.

## Features

- **Route system**: Hierarchical URL routing with slug management
- **Block system**: Modular page builder with Tiptap rich-text blocks
- **Blog**: Generic content posts with tags and routing
- **Menus**: Navigation menu management
- **Redirections**: 301/302 URL redirect management with cache
- **Configuration**: Key-value site configuration
- **Banners**: Image banners with location management
- **Users & Roles**: Filament Shield role-based access control
- **SEO**: Built-in SEO metadata per route
- **Activity Log**: Spatie activity log integration
- **Sitemap**: Auto-generated sitemap.xml

## Tech Stack

- Laravel 11
- Filament 3
- Livewire
- Alpine.js
- Tailwind CSS
- TipTap Editor

## Getting Started

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan filament:install
npm install && npm run build
```

## Admin Panel

Access the admin panel at `/admin`.
