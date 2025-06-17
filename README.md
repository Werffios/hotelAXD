# Sistema de Gestión Hotelera - Hotel AXD

## Descripción General

El sistema de gestión hotelera Hotel AXD es una plataforma completa diseñada para administrar de manera eficiente todas las operaciones relacionadas con un hotel, desde la gestión de habitaciones hasta las reservas y precios según temporada.

## Funcionalidades Principales

### Gestión de Habitaciones
- **Tipos de Habitaciones**: El sistema permite categorizar las habitaciones por tipos (ej. individual, doble, suite).
- **Ubicaciones**: Las habitaciones están organizadas por ubicaciones específicas dentro del hotel.
- **Ocupación**: Control de la capacidad máxima de cada habitación.

### Reservas
- **Disponibilidad**: El sistema verifica automáticamente la disponibilidad de habitaciones para fechas específicas.
- **Gestión de Reservas**: Permite crear, modificar y consultar reservas con fechas de entrada y salida.
- **Estados de Reserva**: Las reservas pueden tener distintos estados como "pendiente" o "confirmada".

### Precios Dinámicos
- **Temporadas**: Configuración de diferentes temporadas (alta, baja, etc.) con fechas específicas.
- **Precios por Temporada**: Los precios de las habitaciones varían según el tipo de habitación y la temporada.
- **Cálculo Automático**: El sistema calcula automáticamente el precio total de una estancia basado en la duración y las temporadas que incluye.

### Gestión de Usuarios
- **Panel de Administración**: Interfaz para usuarios administradores que pueden gestionar todos los aspectos del sistema.
- **Autenticación**: Sistema seguro de inicio de sesión para proteger los datos y operaciones.

## Cómo Acceder

El sistema está disponible en línea para usuarios y administradores en:
**[https://hotelaxd.werffios.com/hotel](https://hotelaxd.werffios.com/hotel)**

## Estructura del Sistema

El sistema está construido con Laravel y utiliza Filament para el panel de administración, proporcionando una interfaz intuitiva y potente para gestionar todos los aspectos del hotel.

### Modelos Principales
- **Habitaciones (Room)**: Representa las habitaciones físicas del hotel.
- **Tipos de Habitación (RoomType)**: Clasifica las habitaciones según sus características.
- **Ubicaciones (RoomSite)**: Define las ubicaciones dentro del hotel.
- **Temporadas (Season)**: Establece los diferentes períodos para precios variables.
- **Precios (RoomPrice)**: Asigna precios específicos según tipo de habitación y temporada.
- **Reservas (Booking)**: Gestiona las reservas de los clientes.

## Para Usuarios

1. Acceda al sistema a través de [https://hotelaxd.werffios.com/hotel](https://hotelaxd.werffios.com/hotel)
2. Explore las habitaciones disponibles para sus fechas deseadas
3. Realice una reserva seleccionando el tipo de habitación y fechas
4. Reciba confirmación de su reserva

## Para Administradores

Los administradores pueden acceder al panel de administración para:
- Gestionar habitaciones y sus características
- Configurar temporadas y precios
- Administrar reservas
- Generar reportes

---

Para cualquier consulta adicional, contacte con el administrador del sistema.
