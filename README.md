# 🧑‍💻 Personal Website – Application Project

Created as part of an application process!  
This site is a simple, interactive way to learn more about me. It features an input prompt where visitors can ask questions about my background, experience, and anything else they’re curious about.

## 🚀 Features

- **Interactive Q&A** – Ask questions about me directly through the website.
- **Modern Laravel stack** – Built with the latest version of Laravel and tools from its ecosystem.

## ⚡️ Tech Stack

This project leverages a modern PHP and Laravel ecosystem:

| Technology | Purpose |
|------------|---------|
| **PHP 8.3** | Backend language |
| **Laravel 12** | Backend framework |
| **Inertia.js (Laravel adapter)** | SPA-like experience using server-side routing |

### Development & Testing Tools

| Tool              | Purpose                     |
|-------------------|-----------------------------|
| **Pest**          | Testing framework           |
| **Larastan**      | Static analysis for Laravel |
| **Laravel Pint**  | Code style fixer            |
| **Rector**        | Automated code upgrades     |
| **Laravel Prism** | Laravel LLM Package         |

## 🛠️ Setup

To get started locally:

```bash
git clone https://github.com/cvtmal/application
cd your-repo-name
composer install
cp .env.example .env
php artisan key:generate
