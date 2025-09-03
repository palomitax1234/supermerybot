# Imagen oficial de PHP 8
FROM php:8.2-cli

# Carpeta de trabajo
WORKDIR /app

# Copiar todo el repo al contenedor
COPY . /app

# Puerto donde correrá el servidor
EXPOSE 10000

# Levanta el servidor embebido de PHP sirviendo /app
CMD ["php", "-S", "0.0.0.0:10000", "-t", "/app"]
