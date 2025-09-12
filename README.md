# Sistema de Cobranza Laravel

Sistema web de cobranza desarrollado con Laravel 12 y Vite para la gestión de clientes, empleados y timbrados.

## Requisitos del Sistema

- **PHP**: 8.2 o superior
- **Node.js**: 20.19.0 o superior
- **Composer**: Para dependencias de PHP
- **NPM**: Para dependencias de JavaScript
- **MySQL** o **SQLite**: Base de datos

## Instalación

### 1. Clonar el repositorio
```bash
git clone https://github.com/emmelaineglz/web-nxn-laravel-cobranza.git
cd web-nxn-laravel-cobranza
```

### 2. Instalar dependencias
```bash
# Dependencias de PHP
composer install

# Dependencias de Node.js
npm install
```

### 3. Configurar variables de entorno
```bash
# Copiar el archivo de ejemplo (si existe)
cp .env.example .env

# O crear manualmente el archivo .env con la siguiente configuración:
```

Contenido mínimo del archivo `.env`:
```env
APP_NAME="Sistema Cobranza"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de datos SQLite (recomendado para desarrollo)
DB_CONNECTION=sqlite

# API Configuration (configurar con valores reales)
EMP_API_BASE_URL=https://api.ejemplo.com
EMP_API_KEY=tu_api_key_aqui

# Otras configuraciones...
LOG_CHANNEL=stack
SESSION_DRIVER=database
CACHE_STORE=database
```

### 4. Generar clave de aplicación
```bash
php artisan key:generate
```

### 5. Preparar la base de datos
```bash
# Para SQLite, crear el archivo de base de datos
touch database/database.sqlite

# Ejecutar migraciones
php artisan migrate:fresh

# Poblar con datos iniciales
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=UserSeeder2
```

### 6. Levantar los servidores

**Terminal 1 - Servidor Laravel:**
```bash
php artisan serve
```

**Terminal 2 - Servidor Vite (para assets):**
```bash
npm run dev
```

## Acceso al Sistema

### URLs
- **Aplicación**: http://127.0.0.1:8000
- **Login**: http://127.0.0.1:8000/login
- **Dashboard**: http://127.0.0.1:8000/dashboard

### Credenciales por Defecto

**Usuario Administrador:**
- Email: `admin@correo.com`
- Contraseña: `password123`

**Usuario Regular:**
- Email: `user@correo.com`
- Contraseña: `user123`

**Usuario de Prueba:**
- Email: `test@example.com`
- Contraseña: `password`

## Estructura del Proyecto

```
app/
├── Http/Controllers/     # Controladores
├── Models/              # Modelos Eloquent
├── Services/            # Servicios de API
└── ...

database/
├── migrations/          # Migraciones de BD
├── seeders/            # Datos iniciales
└── database.sqlite     # Base de datos SQLite

resources/
├── views/              # Vistas Blade
├── css/                # Estilos
└── js/                 # JavaScript

routes/
└── web.php             # Rutas web
```

## Configuración de MySQL (Opcional)

Si prefieres usar MySQL en lugar de SQLite:

1. **Instalar y configurar MySQL**
2. **Crear base de datos:**
   ```sql
   CREATE DATABASE laravel_cobranza;
   ```
3. **Actualizar .env:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel_cobranza
   DB_USERNAME=root
   DB_PASSWORD=tu_password
   ```
4. **Ejecutar migraciones nuevamente:**
   ```bash
   php artisan migrate:fresh --seed
   ```

## Comandos Útiles

```bash
# Limpiar cache de configuración
php artisan config:clear

# Ver estado de migraciones
php artisan migrate:status

# Acceder a la consola interactiva
php artisan tinker

# Refrescar base de datos con datos
php artisan migrate:fresh --seed
```

## Solución de Problemas

### Error: "No application encryption key"
```bash
php artisan key:generate
```

### Error: "Connection refused" (MySQL)
- Verificar que MySQL esté corriendo
- Verificar credenciales en `.env`
- Alternativamente, usar SQLite

### Error de Node.js/Vite
- Verificar versión de Node.js: `node -v`
- Debe ser 20.19.0 o superior
- Actualizar si es necesario

## Tecnologías Utilizadas

- **Laravel 12**: Framework PHP
- **Vite**: Build tool para assets
- **Blade**: Motor de plantillas
- **SQLite/MySQL**: Base de datos
- **Tailwind CSS**: Framework CSS (si aplica)
- **JavaScript/Vue**: Frontend interactivo

## Contribuciones

Este proyecto está en desarrollo activo. Para contribuir:

1. Clon del repositorio
2. Crear branch para feature a partir del branch develop
3. Commit de cambios
4. Push al branch nuevo
5. Crear Pull Request hacia develop y tagear revisor emmelaine.glz@gmail.com
