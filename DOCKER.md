# Docker Setup for The Invisible Cities

This project has been dockerized to make development and deployment easier. The setup includes:

- PHP 8.2 Apache container for the Kirby CMS application
- Caddy as a reverse proxy for handling SSL

## Development Setup

### Prerequisites

- Docker
- Docker Compose

### Getting Started

1. Clone the repository:
   ```
   git clone https://github.com/hemorej/invis.git
   cd invis
   ```

2. Create necessary directories for Caddy:
   ```
   mkdir -p caddy/data caddy/config
   ```

3. Build and start the containers:
   ```
   docker-compose up -d
   ```

4. The site will be available at:
   - https://localhost

### Development Workflow

- Content in the `content` directory is mounted as a volume, so changes will be directly reflected in the container.
- Configuration in `site/config` is also mounted as a volume.
- The cache directory is mounted as a volume to persist cache between restarts.
- Logs are persisted to the host machine.

## Production Deployment

For production deployment:

1. Uncomment and update the domain section in `caddy/Caddyfile` with your actual domain name.

2. Make sure all environment variables are properly set for a production environment in `site/config/config.php`.

3. Build and start the containers:
   ```
   docker-compose up -d
   ```

## Container Management

- Start containers: `docker-compose up -d`
- Stop containers: `docker-compose down`
- View logs: `docker-compose logs -f`
- Rebuild containers: `docker-compose up -d --build`

## Volumes

The setup uses the following volumes:

- `./content:/var/www/html/content` - Content files
- `./site/config:/var/www/html/site/config` - Site configuration
- `./site/cache:/var/www/html/site/cache` - Cache files
- `./logs:/var/www/html/logs` - Log files
- `./caddy/data:/data` - Caddy data (certificates, etc.)
- `./caddy/config:/config` - Caddy configuration

## Additional Configuration

### SSL Certificates

Caddy automatically obtains and renews SSL certificates from Let's Encrypt when using a public domain. For local development, Caddy uses self-signed certificates.

### PHP Configuration

PHP settings can be adjusted by environment variables in `docker-compose.yml`:

- `PHP_MEMORY_LIMIT`: Default is 256M
- `UPLOAD_MAX_FILESIZE`: Default is 100M
- `POST_MAX_SIZE`: Default is 100M