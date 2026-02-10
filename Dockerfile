# Dockerfile multi-stage para aplicación PHP segura
FROM php:8.2-fpm-alpine AS base

# Metadatos
LABEL maintainer="Secure App Team"
LABEL description="Secure PHP Application with Authentication System"
LABEL version="1.0.0"

# Variables de entorno
ENV TZ=America/Mexico_City
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

# Instalar dependencias del sistema
RUN apk add --no-cache \
    bash \
    tzdata \
    nginx \
    supervisor \
    mysql-client \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone

# Instalar extensiones PHP
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    opcache

# Configurar PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Configurar PHP-FPM
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Crear usuario no-root para la aplicación
RUN addgroup -g 1000 appuser \
    && adduser -D -u 1000 -G appuser appuser

# Crear directorios necesarios
RUN mkdir -p /var/www/html /var/log/php /var/log/nginx \
    && chown -R appuser:appuser /var/www/html /var/log/php /var/log/nginx

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar código de la aplicación
COPY --chown=appuser:appuser . .

# Configurar permisos
RUN chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/logs \
    && chown -R appuser:appuser /var/www/html

# Configurar Nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Configurar Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php-fpm -t && curl -f http://localhost/api.php?path=health || exit 1

# Exponer puerto
EXPOSE 80

# Cambiar a usuario no-root
USER appuser

# Comando de inicio
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

# ============================================
# Stage de desarrollo
# ============================================
FROM base AS development

USER root

# Instalar herramientas de desarrollo
RUN apk add --no-cache \
    git \
    vim \
    curl

# Habilitar Xdebug para desarrollo
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

USER appuser

# ============================================
# Stage de producción
# ============================================
FROM base AS production

# Configuración adicional de seguridad para producción
RUN rm -rf /var/cache/apk/* \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Variables de entorno de producción
ENV APP_ENV=production
ENV APP_DEBUG=false
