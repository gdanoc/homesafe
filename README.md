# HomeSafe

Plataforma en línea de bienes raíces y muebles: permite buscar propiedades, contactar agentes inmobiliarios en tiempo real y comprar muebles para decorar el hogar.

> La documentación original del proyecto fue elaborada en inglés; este README está en español.

## Descripción

HomeSafe busca resolver un problema común al buscar vivienda: sitios con poca información, sin fotos claras ni ubicación, y procesos de compra poco confiables. La plataforma conecta clientes con agentes inmobiliarios mediante un chat en tiempo real y ofrece además una tienda de muebles para decorar la propiedad.

## Características

* Registro e inicio de sesión (cliente, agente inmobiliario, administrador)
* Búsqueda de propiedades con mapa (ubicación, habitaciones, precio, imágenes)
* Chat en tiempo real con el agente inmobiliario
* Compra de muebles con carrito y checkout (PayPal Sandbox)
* Planes de suscripción mensual para convertirse en vendedor/agente
* Seguimiento de pedidos (en espera, listo para entrega, entregado)
* Panel de administrador: CRUD de muebles, repartidores, carrusel de imágenes, estadísticas y ventas
* Recuperación de contraseña vía correo

## Tecnologías

* PHP, Python
* PostgreSQL
* Laravel, XAMPP
* Firebase (chat en tiempo real)
* JavaScript, Leaflet (mapas)
* PayPal Sandbox
* Raspberry Pi (hosting de base de datos durante el desarrollo)

## Instalación

1. Clonar el repositorio.
2. Configurar un servidor local (XAMPP/Laragon) apuntando a la carpeta del proyecto.
3. Crear la base de datos `homesafe` en PostgreSQL.
4. En `includes/conn.php` (y en los demás archivos que definen la conexión), reemplazar `TU\_PASSWORD\_DE\_BASE\_DE\_DATOS` por tu contraseña local de PostgreSQL.
5. Configurar las credenciales de Firebase propias si se quiere probar el chat en tiempo real.
6. Levantar el servidor y acceder desde el navegador.

## Base de datos

PostgreSQL, base de datos `homesafe`.

## Autores

* Diego Leonardo Alemán Granados
* Rafael Mauricio Escobar Marroquín
* Gerardo Daniel Orellana Campos

Proyecto Técnico Científico 2025 — Software Development II — Colegio Salesiano Santa Cecilia.

