# Bugtracker

A lightweight, self-hosted issue and bug tracking application designed for private projects and small development environments.

The application provides a simple interface for managing issues, tracking their status and assignments, and organizing development work without the overhead of a full project management or source-control platform.

It is built as a self-hosted web application with a focus on simplicity, maintainability, and easy deployment using Docker.

## Requirements

The application is designed to run as a self-hosted web application using Docker.

### Runtime & Infrastructure

* **Docker** and **Docker Compose**
* **PHP 8.3+**
* A supported SQL database (via Doctrine DBAL/ORM)

### Core Dependencies

The application is built around the following core components:

* **Slim Framework 4** — HTTP application framework
* **Doctrine ORM** — object-relational mapping and database access
* **Doctrine Migrations** — database schema migrations
* **PHP-DI** — dependency injection
* **Twig** — server-side HTML templating
* **HTMX** — dynamic browser/server interactions without a JavaScript framework
* **Pico CSS** — lightweight frontend styling
* **Font Awesome** — interface icons

Additional Composer dependencies are managed through `composer.json` and `composer.lock`.

For licensing information regarding this project and its third-party dependencies, see [`LICENSE.md`](./LICENSE.md) and [`THIRD-PARTY-NOTICES.md`](./THIRD-PARTY-NOTICES.md).

## Installation

The recommended way to install the application is using Docker Compose.

1. Clone the repository:

   ```bash
   git clone https://github.com/reuter-max/bugtracker.git
   cd your-project
   ```

2. Create your environment configuration:

   ```bash
   cp .env.example .env
   ```

   Adjust the configuration values in `.env` to match your environment, especially the database connection and application settings.

3. Start the application:

   ```bash
   docker compose up -d
   ```

4. The application will then be available at the configured HTTP port.

For production deployments, make sure the application is placed behind a suitable reverse proxy and that persistent storage is configured for the database and application data.
