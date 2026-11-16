-- Base de datos TAMPU (entrega final)
-- Importar en phpMyAdmin. Usuario admin: admin@tampu.pe / admin123
-- Los demás usuarios de prueba tienen la contraseña 123456

-- si ya existe una base tampu antigua se reemplaza
DROP DATABASE IF EXISTS tampu;
CREATE DATABASE tampu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SET NAMES utf8mb4;
USE tampu;
SET FOREIGN_KEY_CHECKS = 0;

/*M!999999\- enable the sandbox mode */ 

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `contactos`;
CREATE TABLE `contactos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `asunto` varchar(80) NOT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `conversaciones`;
CREATE TABLE `conversaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_a_id` bigint(20) unsigned NOT NULL,
  `usuario_b_id` bigint(20) unsigned NOT NULL,
  `habitacion_id` bigint(20) unsigned DEFAULT NULL,
  `trabajo_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversaciones_usuario_a_id_foreign` (`usuario_a_id`),
  KEY `conversaciones_usuario_b_id_foreign` (`usuario_b_id`),
  KEY `conversaciones_habitacion_id_foreign` (`habitacion_id`),
  KEY `conversaciones_trabajo_id_foreign` (`trabajo_id`),
  CONSTRAINT `conversaciones_habitacion_id_foreign` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conversaciones_trabajo_id_foreign` FOREIGN KEY (`trabajo_id`) REFERENCES `trabajos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conversaciones_usuario_a_id_foreign` FOREIGN KEY (`usuario_a_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversaciones_usuario_b_id_foreign` FOREIGN KEY (`usuario_b_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `conversaciones` VALUES (1,11,2,4,NULL,'2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `conversaciones` VALUES (2,10,6,NULL,1,'2026-11-16 04:10:22','2026-11-16 04:10:22');
DROP TABLE IF EXISTS `favoritos`;
CREATE TABLE `favoritos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `habitacion_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favoritos_usuario_id_habitacion_id_unique` (`usuario_id`,`habitacion_id`),
  KEY `favoritos_habitacion_id_foreign` (`habitacion_id`),
  CONSTRAINT `favoritos_habitacion_id_foreign` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favoritos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `habitaciones`;
CREATE TABLE `habitaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `descripcion` text NOT NULL,
  `distrito` varchar(40) NOT NULL,
  `direccion` varchar(150) NOT NULL,
  `cerca_de` varchar(80) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `precio` decimal(7,2) NOT NULL,
  `tipo` enum('individual','compartida','minidepartamento') NOT NULL,
  `servicios` varchar(200) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `estado` enum('disponible','ocupada') NOT NULL DEFAULT 'disponible',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `habitaciones_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `habitaciones_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `habitaciones` VALUES (1,2,'Habitación amoblada cerca de la U. Continental','Habitación amplia y luminosa en segundo piso, con escritorio, cama de 1.5 plazas y ropero. Ambiente tranquilo para estudiar. Ingreso independiente.','Wanchaq','Av. Los Incas 512','U. Continental',-13.5248000,-71.9655000,380.00,'individual','Agua y luz, Wifi, Amoblado','img/habitaciones/h1.jpg','disponible','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `habitaciones` VALUES (2,3,'Cuarto económico en Santiago','Cuarto sencillo con cama, mesa y estante. Baño compartido con otro estudiante. Cerca de paraderos.','Santiago','Jr. Arcopata 230','IST Túpac Amaru',-13.5185000,-71.9858000,250.00,'individual','Agua y luz, Wifi','img/habitaciones/h2.jpg','disponible','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `habitaciones` VALUES (3,4,'Habitación con baño propio – San Sebastián','Habitación con baño propio y terma. Edificio con portón y vigilancia. A seis minutos caminando de la U. Andina.','San Sebastián','Calle Las Rosas 145','U. Andina',-13.5312000,-71.9381000,450.00,'individual','Agua y luz, Wifi, Baño propio, Agua caliente','img/habitaciones/h3.jpg','disponible','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `habitaciones` VALUES (4,2,'Cuarto cerca de la UNSAAC','Cuarto en casa familiar a cinco minutos de la UNSAAC. Se puede usar la cocina en horarios acordados.','Cusco','Av. de la Cultura 1020','UNSAAC',-13.5221000,-71.9588000,320.00,'individual','Agua y luz, Wifi, Cocina','img/habitaciones/h4.jpg','disponible','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `habitaciones` VALUES (5,4,'Minidepartamento para estudiante','Minidepartamento con kitchenette, baño propio y sala de estudio. Ideal para quien busca privacidad.','San Sebastián','Urb. Tupac Amaru B-12','U. Andina',-13.5336000,-71.9329000,550.00,'minidepartamento','Agua y luz, Wifi, Baño propio, Cocina, Amoblado','img/habitaciones/h5.jpg','disponible','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `habitaciones` VALUES (6,3,'Habitación compartida en Wanchaq','Habitación para dos estudiantes, cada uno con su cama y escritorio. Zona segura y con tiendas cerca.','Wanchaq','Calle Tacna 340','U. Continental',-13.5232000,-71.9682000,220.00,'compartida','Agua y luz, Wifi, Lavandería','img/habitaciones/h6.jpg','disponible','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `habitaciones` VALUES (7,2,'Habitación amplia en el centro','Habitación grande en casona del centro, con ventana a la calle y mucha luz natural.','Cusco','Calle Saphy 455','UNSAAC',-13.5152000,-71.9822000,400.00,'individual','Agua y luz, Wifi, Agua caliente','img/habitaciones/h7.jpg','disponible','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `habitaciones` VALUES (8,3,'Cuarto para estudiante – Santiago','Cuarto con cama de plaza y media y ropero. Se aceptan estudiantes de institutos.','Santiago','Av. Antonio Lorena 780','IST Túpac Amaru',-13.5226000,-71.9831000,270.00,'individual','Agua y luz, Lavandería','img/habitaciones/h8.jpg','disponible','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `habitaciones` VALUES (9,4,'Cuarto con vista – San Jerónimo','Cuarto en tercer piso con vista a los cerros. Movilidad directa a la U. Andina.','San Jerónimo','Av. La Cultura 2450','U. Andina',-13.5452000,-71.8893000,280.00,'individual','Agua y luz, Wifi','img/habitaciones/h9.jpg','disponible','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `habitaciones` VALUES (10,14,'Cuarto amplio a solo S/ 150 en el centro','Cuarto grande, quedan pocos. Para separarlo deposita 300 soles al número de cuenta que te paso por mensaje.','Cusco','Calle Nueva Alta 88','UNSAAC',-13.5195000,-71.9745000,150.00,'individual','Agua y luz, Wifi','img/habitaciones/h10.jpg','disponible','2026-11-16 04:10:22','2026-11-16 04:10:22');
DROP TABLE IF EXISTS `horarios`;
CREATE TABLE `horarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `dia` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
  `turno` enum('manana','tarde','noche') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `horarios_usuario_id_dia_turno_unique` (`usuario_id`,`dia`,`turno`),
  CONSTRAINT `horarios_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `horarios` VALUES (1,10,'lunes','manana');
INSERT INTO `horarios` VALUES (6,10,'lunes','tarde');
INSERT INTO `horarios` VALUES (2,10,'martes','manana');
INSERT INTO `horarios` VALUES (3,10,'miercoles','manana');
INSERT INTO `horarios` VALUES (7,10,'miercoles','tarde');
INSERT INTO `horarios` VALUES (4,10,'jueves','manana');
INSERT INTO `horarios` VALUES (5,10,'viernes','manana');
DROP TABLE IF EXISTS `mensajes`;
CREATE TABLE `mensajes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversacion_id` bigint(20) unsigned NOT NULL,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `texto` text NOT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mensajes_conversacion_id_foreign` (`conversacion_id`),
  KEY `mensajes_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `mensajes_conversacion_id_foreign` FOREIGN KEY (`conversacion_id`) REFERENCES `conversaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mensajes_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mensajes` VALUES (1,1,11,'Hola señora Rosa, ¿el cuarto tiene escritorio?',1,'2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `mensajes` VALUES (2,1,2,'Hola Ana, sí tiene escritorio y una silla. Te espero el día de la visita.',1,'2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `mensajes` VALUES (3,1,11,'Perfecto, muchas gracias.',0,'2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `mensajes` VALUES (4,2,10,'Buenas tardes, ¿qué días empezaría?',1,'2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `mensajes` VALUES (5,2,6,'Hola Luis, puedes empezar este martes a las 2 pm en el local de Santiago.',0,'2026-11-16 04:10:22','2026-11-16 04:10:22');
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` VALUES (1,'2026_11_01_000001_create_usuarios_table',1);
INSERT INTO `migrations` VALUES (2,'2026_11_01_000002_create_habitaciones_table',1);
INSERT INTO `migrations` VALUES (3,'2026_11_01_000003_create_trabajos_table',1);
INSERT INTO `migrations` VALUES (4,'2026_11_15_000001_create_modulos_finales',1);
INSERT INTO `migrations` VALUES (5,'2026_11_16_000001_create_contactos_y_sesiones',1);
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` VALUES ('9385822e-4b37-450f-966c-df7a9737713c','App\\Notifications\\Aviso','App\\Models\\Usuario',10,'{\"texto\":\"Minimarket Wayra acept\\u00f3 tu postulaci\\u00f3n a \\\"Repartidor en bicicleta\\\"\",\"url\":\"\\/estudiante\",\"icono\":\"briefcase\"}',NULL,'2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `notifications` VALUES ('acd92cc3-ea08-4b79-9428-dc376c54ccf3','App\\Notifications\\Aviso','App\\Models\\Usuario',3,'{\"texto\":\"Ana Quispe Ccasa quiere visitar \\\"Habitaci\\u00f3n compartida en Wanchaq\\\"\",\"url\":\"\\/arrendador\\/solicitudes\",\"icono\":\"house-door\"}',NULL,'2026-11-16 04:10:22','2026-11-16 04:10:22');
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `postulaciones`;
CREATE TABLE `postulaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trabajo_id` bigint(20) unsigned NOT NULL,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `mensaje` varchar(300) DEFAULT NULL,
  `estado` enum('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `postulaciones_trabajo_id_usuario_id_unique` (`trabajo_id`,`usuario_id`),
  KEY `postulaciones_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `postulaciones_trabajo_id_foreign` FOREIGN KEY (`trabajo_id`) REFERENCES `trabajos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `postulaciones_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `postulaciones` VALUES (1,1,10,'Tengo bicicleta propia y conozco bien Santiago.','aceptada','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `postulaciones` VALUES (2,6,10,'Tengo inglés básico.','pendiente','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `postulaciones` VALUES (3,5,11,'Estoy en sexto ciclo de Ingeniería de Sistemas.','pendiente','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `postulaciones` VALUES (4,4,13,NULL,'aceptada','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `postulaciones` VALUES (5,1,12,NULL,'rechazada','2026-11-16 04:10:22','2026-11-16 04:10:22');
DROP TABLE IF EXISTS `reportes`;
CREATE TABLE `reportes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `habitacion_id` bigint(20) unsigned DEFAULT NULL,
  `trabajo_id` bigint(20) unsigned DEFAULT NULL,
  `motivo` varchar(60) NOT NULL,
  `detalle` varchar(500) DEFAULT NULL,
  `estado` enum('pendiente','descartado','sancionado') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reportes_usuario_id_foreign` (`usuario_id`),
  KEY `reportes_habitacion_id_foreign` (`habitacion_id`),
  KEY `reportes_trabajo_id_foreign` (`trabajo_id`),
  CONSTRAINT `reportes_habitacion_id_foreign` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reportes_trabajo_id_foreign` FOREIGN KEY (`trabajo_id`) REFERENCES `trabajos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reportes_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reportes` VALUES (1,13,10,NULL,'Pide adelanto sin visita','Me pidió depositar 300 soles para separar el cuarto sin dejarme verlo.','pendiente','2026-11-16 04:10:22','2026-11-16 04:10:22');
DROP TABLE IF EXISTS `resenas`;
CREATE TABLE `resenas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `habitacion_id` bigint(20) unsigned NOT NULL,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `estrellas` tinyint(3) unsigned NOT NULL,
  `comentario` varchar(500) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resenas_habitacion_id_usuario_id_unique` (`habitacion_id`,`usuario_id`),
  KEY `resenas_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `resenas_habitacion_id_foreign` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resenas_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `resenas` VALUES (1,1,10,5,'La habitación es igual que en las fotos. La señora Rosa es muy amable y la zona es tranquila.','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `resenas` VALUES (2,1,12,4,'Buena habitación y cerca de la universidad. El wifi a veces falla en las noches.','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `resenas` VALUES (3,3,13,5,'El baño propio y el agua caliente valen la pena. Muy seguro el edificio.','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `resenas` VALUES (4,2,12,3,'Es económico pero el baño compartido es pequeño. Para el precio está bien.','2026-11-16 04:10:22','2026-11-16 04:10:22');
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `solicitudes`;
CREATE TABLE `solicitudes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `habitacion_id` bigint(20) unsigned NOT NULL,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `fecha_visita` date NOT NULL,
  `mensaje` varchar(300) DEFAULT NULL,
  `estado` enum('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitudes_habitacion_id_foreign` (`habitacion_id`),
  KEY `solicitudes_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `solicitudes_habitacion_id_foreign` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `solicitudes_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `solicitudes` VALUES (1,4,11,'2026-11-17','Hola, soy de Abancay y estudio en la UNSAAC. ¿Puedo ir el sábado?','aceptada','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `solicitudes` VALUES (2,6,11,'2026-11-19','¿La habitación compartida es solo para mujeres?','pendiente','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `solicitudes` VALUES (3,1,10,'2026-11-09','Quisiera verla antes de mudarme.','aceptada','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `solicitudes` VALUES (4,1,12,'2026-11-05','Hola, estudio en la Continental.','aceptada','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `solicitudes` VALUES (5,3,13,'2026-11-07','¿Puedo ir por la tarde?','aceptada','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `solicitudes` VALUES (6,2,12,'2026-11-03','Me interesa, ¿sigue disponible?','aceptada','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `solicitudes` VALUES (7,7,11,'2026-11-12','Hola, ¿puedo verla el lunes?','aceptada','2026-11-16 04:10:22','2026-11-16 04:10:22');
DROP TABLE IF EXISTS `trabajo_horarios`;
CREATE TABLE `trabajo_horarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trabajo_id` bigint(20) unsigned NOT NULL,
  `dia` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
  `turno` enum('manana','tarde','noche') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trabajo_horarios_trabajo_id_dia_turno_unique` (`trabajo_id`,`dia`,`turno`),
  CONSTRAINT `trabajo_horarios_trabajo_id_foreign` FOREIGN KEY (`trabajo_id`) REFERENCES `trabajos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trabajo_horarios` VALUES (1,1,'martes','tarde');
INSERT INTO `trabajo_horarios` VALUES (2,1,'jueves','tarde');
INSERT INTO `trabajo_horarios` VALUES (3,1,'sabado','tarde');
INSERT INTO `trabajo_horarios` VALUES (4,2,'sabado','manana');
INSERT INTO `trabajo_horarios` VALUES (5,3,'lunes','manana');
INSERT INTO `trabajo_horarios` VALUES (6,3,'martes','manana');
INSERT INTO `trabajo_horarios` VALUES (7,3,'miercoles','manana');
INSERT INTO `trabajo_horarios` VALUES (8,3,'jueves','manana');
INSERT INTO `trabajo_horarios` VALUES (9,3,'viernes','manana');
INSERT INTO `trabajo_horarios` VALUES (10,4,'lunes','noche');
INSERT INTO `trabajo_horarios` VALUES (11,4,'martes','noche');
INSERT INTO `trabajo_horarios` VALUES (12,4,'miercoles','noche');
INSERT INTO `trabajo_horarios` VALUES (13,4,'jueves','noche');
INSERT INTO `trabajo_horarios` VALUES (14,4,'viernes','noche');
INSERT INTO `trabajo_horarios` VALUES (15,5,'lunes','tarde');
INSERT INTO `trabajo_horarios` VALUES (16,5,'miercoles','tarde');
INSERT INTO `trabajo_horarios` VALUES (17,5,'viernes','tarde');
INSERT INTO `trabajo_horarios` VALUES (18,6,'sabado','tarde');
INSERT INTO `trabajo_horarios` VALUES (19,6,'sabado','noche');
INSERT INTO `trabajo_horarios` VALUES (20,6,'domingo','tarde');
INSERT INTO `trabajo_horarios` VALUES (21,6,'domingo','noche');
INSERT INTO `trabajo_horarios` VALUES (22,7,'sabado','manana');
INSERT INTO `trabajo_horarios` VALUES (23,7,'sabado','tarde');
DROP TABLE IF EXISTS `trabajos`;
CREATE TABLE `trabajos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `empresa` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `distrito` varchar(40) NOT NULL,
  `modalidad` enum('medio tiempo','por horas','practicas') NOT NULL,
  `pago` decimal(7,2) NOT NULL,
  `pago_por` enum('hora','dia','mes') NOT NULL,
  `estado` enum('activa','cerrada') NOT NULL DEFAULT 'activa',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trabajos_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `trabajos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trabajos` VALUES (1,6,'Repartidor en bicicleta','Minimarket Wayra','Reparto de pedidos en la zona de Santiago. Se da la bicicleta.','Santiago','medio tiempo',40.00,'dia','activa','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `trabajos` VALUES (2,7,'Soporte técnico sábados','Andes Tech SAC','Mantenimiento de computadoras e instalación de programas.','Wanchaq','por horas',70.00,'dia','activa','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `trabajos` VALUES (3,8,'Ayudante de cocina (mañanas)','Restaurante Inti Raymi','Apoyo en la preparación de desayunos y almuerzos.','Cusco','medio tiempo',50.00,'dia','activa','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `trabajos` VALUES (4,6,'Atención en minimarket (noches)','Minimarket Wayra','Atención al cliente y caja en turno de noche.','San Sebastián','medio tiempo',45.00,'dia','activa','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `trabajos` VALUES (5,7,'Practicante de sistemas','Andes Tech SAC','Apoyo en desarrollo web y soporte a usuarios. Para estudiantes de Ingeniería de Sistemas.','Wanchaq','practicas',1025.00,'mes','activa','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `trabajos` VALUES (6,8,'Mozo/a fin de semana','Restaurante Inti Raymi','Atención en mesas los fines de semana. Se valora inglés básico.','Cusco','medio tiempo',60.00,'dia','activa','2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `trabajos` VALUES (7,9,'Guía de apoyo turístico','Qhapaq Tours','Acompañar a grupos de turistas en el centro histórico.','Cusco','por horas',80.00,'dia','activa','2026-11-16 04:10:22','2026-11-16 04:10:22');
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `dni` char(8) NOT NULL,
  `celular` varchar(9) DEFAULT NULL,
  `procedencia` varchar(60) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `rol` enum('estudiante','arrendador','empleador','admin') NOT NULL DEFAULT 'estudiante',
  `verificacion` enum('no_aplica','pendiente','verificado','rechazado') NOT NULL DEFAULT 'no_aplica',
  `suspendido` tinyint(1) NOT NULL DEFAULT 0,
  `documento` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_email_unique` (`email`),
  UNIQUE KEY `usuarios_dni_unique` (`dni`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` VALUES (1,'Administrador TAMPU','admin@tampu.pe','70000001',NULL,NULL,NULL,'admin','no_aplica',0,NULL,'$2y$12$xrJffAOPKzeo279iTgpzkOs09XWhtjOzJFqspXEipS4QyJzVRtaZy',NULL,'2026-11-16 04:10:19','2026-11-16 04:10:19');
INSERT INTO `usuarios` VALUES (2,'Rosa Huamán Quispe','rosa@tampu.pe','41234567','984111222',NULL,NULL,'arrendador','verificado',0,NULL,'$2y$12$90KD7xRF4ZeC1GG5H8fqcecjSeEmjZ7f012MUSaZZGrkpJPCf0kJu',NULL,'2026-11-16 04:10:19','2026-11-16 04:10:19');
INSERT INTO `usuarios` VALUES (3,'Julio Ccori Mamani','julio@tampu.pe','42345678','984333444',NULL,NULL,'arrendador','verificado',0,NULL,'$2y$12$gechdriP09rqZ/Dt4uC2DOhS.Y0gQdmIKFX/92HV1XxLUrFqZhLjy',NULL,'2026-11-16 04:10:19','2026-11-16 04:10:19');
INSERT INTO `usuarios` VALUES (4,'Carmen Mamani Huallpa','carmen@tampu.pe','43456789','984555666',NULL,NULL,'arrendador','verificado',0,NULL,'$2y$12$UO42yZxjU3VsavX9lg0nLeSbMp4yPOXxSNOyk132lFxv4N9zIhUh2',NULL,'2026-11-16 04:10:20','2026-11-16 04:10:20');
INSERT INTO `usuarios` VALUES (5,'Pedro Ccahuana Rojas','pedro@tampu.pe','44567890','984777888',NULL,NULL,'arrendador','pendiente',0,'documentos/demo-pedro.png','$2y$12$Fh0MqD9C4WWcw.z0uIjBMe9PwCZ.AgVsx4.l7KjB3HrHSp3KjUD.C',NULL,'2026-11-16 04:10:20','2026-11-16 04:10:20');
INSERT INTO `usuarios` VALUES (6,'Lucía Ttito Condori','wayra@tampu.pe','45678901','974111222',NULL,NULL,'empleador','verificado',0,NULL,'$2y$12$pZxDIEHmXA/UuiHMiNFVDuYDGUZw9ATv4MtxIfyM4ayCa/A6U1N2y',NULL,'2026-11-16 04:10:20','2026-11-16 04:10:20');
INSERT INTO `usuarios` VALUES (7,'Marco Paredes Soto','andestech@tampu.pe','46789012','974333444',NULL,NULL,'empleador','verificado',0,NULL,'$2y$12$le9pLZsCzZgO2D714jB..eVsbxBosYE9gHe4ZLpHMsDU56a2q7xT2',NULL,'2026-11-16 04:10:20','2026-11-16 04:10:20');
INSERT INTO `usuarios` VALUES (8,'Elena Condori Apaza','intiraymi@tampu.pe','47890123','974555666',NULL,NULL,'empleador','verificado',0,NULL,'$2y$12$y7u0T8LXHKXqCr/xozZ8geyDVBf43v4X2vxJyevEQI6x9ylyw2Mf.',NULL,'2026-11-16 04:10:21','2026-11-16 04:10:21');
INSERT INTO `usuarios` VALUES (9,'Diego Huallpa Cusi','qhapaq@tampu.pe','48901234','974777888',NULL,NULL,'empleador','pendiente',0,'documentos/demo-qhapaq.png','$2y$12$MhFa0CGzE1sTyBjgRaLgvuG5uBkRgPiwz956LVuhHHxibqNyKUZrG',NULL,'2026-11-16 04:10:21','2026-11-16 04:10:21');
INSERT INTO `usuarios` VALUES (10,'Luis Mamani Choque','luis@tampu.pe','72345678','951222333','Sicuani',NULL,'estudiante','no_aplica',0,NULL,'$2y$12$ZcE04z1KFUMBO44bAEbAleXkWZyMmMf0qd8GtjKVI7jeSO.7AzWHW',NULL,'2026-11-16 04:10:21','2026-11-16 04:10:21');
INSERT INTO `usuarios` VALUES (11,'Ana Quispe Ccasa','ana@tampu.pe','73456789','951444555','Abancay',NULL,'estudiante','no_aplica',0,NULL,'$2y$12$pFMF8Ix07SqfQucHSIX4JO5gy4tGhhN0.0oEzl/HdP0PeGFJrFwOC',NULL,'2026-11-16 04:10:21','2026-11-16 04:10:21');
INSERT INTO `usuarios` VALUES (12,'Rocío Apaza Layme','rocio@tampu.pe','74567890','951666777','Puno',NULL,'estudiante','no_aplica',0,NULL,'$2y$12$uLT8bpwFopuO9CxpXuJMnuHDRD0KSoj3FkxCQIbEsSIoWVspaEucq',NULL,'2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `usuarios` VALUES (13,'Kevin Huamán Cusi','kevin@tampu.pe','75678901','951888999','Quillabamba',NULL,'estudiante','no_aplica',0,NULL,'$2y$12$XgcdSZdmQom7NB3FX9QIsexWrJcDi9Z.SO/YO6ZWYfQqG1uorBcxW',NULL,'2026-11-16 04:10:22','2026-11-16 04:10:22');
INSERT INTO `usuarios` VALUES (14,'Mario Sullca Ttito','mario@tampu.pe','49012345','984999000',NULL,NULL,'arrendador','verificado',0,NULL,'$2y$12$WBS5CD0W/ao522eiuXzmx.atmf68ui6xHXldAqrb/5tBE5p875q1.',NULL,'2026-11-16 04:10:22','2026-11-16 04:10:22');


SET FOREIGN_KEY_CHECKS = 1;
