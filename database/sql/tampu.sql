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

INSERT INTO `conversaciones` VALUES (1,11,2,4,NULL,'2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `conversaciones` VALUES (2,10,6,NULL,1,'2026-11-17 00:25:22','2026-11-17 00:25:22');
DROP TABLE IF EXISTS `eventos_seguridad`;
CREATE TABLE `eventos_seguridad` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tipo` varchar(30) NOT NULL,
  `detalle` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `navegador` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `eventos_seguridad_usuario_id_foreign` (`usuario_id`),
  KEY `eventos_seguridad_tipo_created_at_index` (`tipo`,`created_at`),
  CONSTRAINT `eventos_seguridad_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DROP TABLE IF EXISTS `habitacion_fotos`;
CREATE TABLE `habitacion_fotos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `habitacion_id` bigint(20) unsigned NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `descripcion` varchar(80) DEFAULT NULL,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `habitacion_fotos_habitacion_id_foreign` (`habitacion_id`),
  CONSTRAINT `habitacion_fotos_habitacion_id_foreign` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `habitacion_fotos` VALUES (1,1,'img/habitaciones/galeria/dormitorio-02.jpg','Escritorio junto a la ventana',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (2,1,'img/habitaciones/galeria/bano-03.jpg','Baño compartido con terma',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (3,1,'img/habitaciones/galeria/cocina-02.jpg','Cocina de la casa',3,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (4,2,'img/habitaciones/galeria/bano-01.jpg','Baño compartido',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (5,2,'img/habitaciones/galeria/lavanderia-01.jpg','Zona de lavado',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (6,3,'img/habitaciones/galeria/bano-02.jpg','Baño propio con ducha',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (7,3,'img/habitaciones/galeria/dormitorio-11.jpg','Otra vista del cuarto',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (8,3,'img/habitaciones/galeria/estudio-01.jpg','Zona de estudio',3,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (9,4,'img/habitaciones/galeria/cocina-05.jpg','Cocina compartida',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (10,4,'img/habitaciones/galeria/dormitorio-07.jpg','Cama y ropero',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (11,5,'img/habitaciones/galeria/cocina-01.jpg','Kitchenette',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (12,5,'img/habitaciones/galeria/bano-02.jpg','Baño propio',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (13,5,'img/habitaciones/galeria/dormitorio-05.jpg','Dormitorio',3,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (14,5,'img/habitaciones/galeria/estudio-01.jpg','Sala de estudio',4,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (15,6,'img/habitaciones/galeria/dormitorio-13.jpg','Cada uno con su cama',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (16,6,'img/habitaciones/galeria/lavanderia-01.jpg','Lavadora de la casa',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (17,7,'img/habitaciones/galeria/dormitorio-09.jpg','Ventana a la calle',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (18,7,'img/habitaciones/galeria/bano-01.jpg','Baño con agua caliente',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (19,8,'img/habitaciones/galeria/lavanderia-01.jpg','Lavandería',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (20,8,'img/habitaciones/galeria/dormitorio-06.jpg','Ropero y cama',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (21,9,'img/habitaciones/galeria/dormitorio-14.jpg','Mucha luz por la mañana',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (22,9,'img/habitaciones/galeria/bano-03.jpg','Baño del piso',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (23,11,'img/habitaciones/galeria/estudio-01.jpg','Escritorio amplio',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (24,11,'img/habitaciones/galeria/bano-01.jpg','Baño del piso',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (25,11,'img/habitaciones/galeria/cocina-04.jpg','Cocina compartida',3,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (26,12,'img/habitaciones/galeria/bano-03.jpg','Baño con terma',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (27,12,'img/habitaciones/galeria/dormitorio-10.jpg','Otra vista',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (28,13,'img/habitaciones/galeria/dormitorio-08.jpg','Cama de plaza y media',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (29,13,'img/habitaciones/galeria/bano-01.jpg','Baño compartido',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (30,13,'img/habitaciones/galeria/cocina-03.jpg','Cocina',3,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (31,14,'img/habitaciones/galeria/cocina-02.jpg','Kitchenette equipada',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (32,14,'img/habitaciones/galeria/bano-02.jpg','Baño propio',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (33,14,'img/habitaciones/galeria/estudio-01.jpg','Zona de estudio',3,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (34,15,'img/habitaciones/galeria/bano-03.jpg','Baño compartido',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (35,16,'img/habitaciones/galeria/bano-01.jpg','Baño con terma',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (36,16,'img/habitaciones/galeria/cocina-04.jpg','Cocina compartida',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (37,16,'img/habitaciones/galeria/dormitorio-13.jpg','Ropero empotrado',3,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (38,17,'img/habitaciones/galeria/lavanderia-01.jpg','Lavadora',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (39,17,'img/habitaciones/galeria/cocina-05.jpg','Cocina',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (40,18,'img/habitaciones/galeria/bano-02.jpg','Baño propio',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (41,18,'img/habitaciones/galeria/dormitorio-02.jpg','Escritorio',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (42,19,'img/habitaciones/galeria/bano-03.jpg','Baño compartido',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (43,20,'img/habitaciones/galeria/bano-01.jpg','Baño compartido',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (44,21,'img/habitaciones/galeria/dormitorio-11.jpg','Ventana con vista a los techos',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (45,21,'img/habitaciones/galeria/bano-03.jpg','Baño con terma',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (46,22,'img/habitaciones/galeria/cocina-03.jpg','Cocina compartida',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (47,22,'img/habitaciones/galeria/bano-01.jpg','Baño',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (48,23,'img/habitaciones/galeria/cocina-01.jpg','Cocina',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (49,23,'img/habitaciones/galeria/bano-02.jpg','Baño propio',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (50,23,'img/habitaciones/galeria/dormitorio-03.jpg','Dormitorio',3,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (51,23,'img/habitaciones/galeria/estudio-01.jpg','Escritorio',4,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (52,24,'img/habitaciones/galeria/bano-01.jpg','Baño compartido',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (53,25,'img/habitaciones/galeria/lavanderia-01.jpg','Lavandería',1,'2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `habitacion_fotos` VALUES (54,25,'img/habitaciones/galeria/cocina-05.jpg','Cocina',2,'2026-11-17 00:25:24','2026-11-17 00:25:24');
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
  `metros` smallint(5) unsigned DEFAULT NULL,
  `piso` tinyint(3) unsigned DEFAULT NULL,
  `bano` varchar(12) DEFAULT NULL,
  `garantia` decimal(7,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `habitaciones_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `habitaciones_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `habitaciones` VALUES (1,2,'Habitación amoblada en Av. Pachacútec','Habitación amplia y luminosa en segundo piso, con escritorio, cama de 1.5 plazas y ropero. Ambiente tranquilo para estudiar. Ingreso independiente y paradero de buses a la UNSAAC en la esquina.','Wanchaq','Av. Pachacútec 506','UNSAAC',-13.5242495,-71.9696114,380.00,'individual','Agua y luz, Wifi, Amoblado, Agua caliente','img/habitaciones/h1.jpg','disponible','2026-11-17 00:25:22','2026-11-17 00:25:24',14,2,'compartido',380.00);
INSERT INTO `habitaciones` VALUES (2,3,'Cuarto económico en Santiago','Cuarto sencillo con cama, mesa y estante. Baño compartido con otro estudiante. A dos cuadras del Mercado de San Pedro y cerca de los paraderos de la Av. Ejército.','Santiago','Calle Tambopata 230, Urb. Roccopata','UNSAAC',-13.5238400,-71.9829900,250.00,'individual','Agua y luz, Wifi','img/habitaciones/h2.jpg','disponible','2026-11-17 00:25:22','2026-11-17 00:25:24',10,1,'compartido',250.00);
INSERT INTO `habitaciones` VALUES (3,4,'Habitación con baño propio – San Sebastián','Habitación con baño propio y terma. Edificio con portón y vigilancia. A unos ocho minutos caminando del campus Larapa de la U. Andina.','San Sebastián','Calle Kiswar 145, Urb. Túpac Amaru','U. Andina',-13.5347800,-71.9087300,450.00,'individual','Agua y luz, Wifi, Baño propio, Agua caliente','img/habitaciones/h3.jpg','disponible','2026-11-17 00:25:22','2026-11-17 00:25:24',16,3,'propio',450.00);
INSERT INTO `habitaciones` VALUES (4,2,'Cuarto cerca de la UNSAAC','Cuarto en casa familiar a cinco minutos caminando de la UNSAAC y frente al Real Plaza. Se puede usar la cocina en horarios acordados.','Cusco','Pasaje Inca Roca 120, Urb. Manuel Prado','UNSAAC',-13.5207000,-71.9553900,320.00,'individual','Agua y luz, Wifi, Cocina','img/habitaciones/h4.jpg','disponible','2026-11-17 00:25:22','2026-11-17 00:25:24',12,2,'compartido',320.00);
INSERT INTO `habitaciones` VALUES (5,4,'Minidepartamento para estudiante','Minidepartamento con kitchenette, baño propio y sala de estudio. Ideal para quien busca privacidad. Cerca de la Vía Expresa y de la U. Andina.','San Sebastián','Urb. Túpac Amaru Mz. B Lt. 12','U. Andina',-13.5387800,-71.9070200,550.00,'minidepartamento','Agua y luz, Wifi, Baño propio, Cocina, Amoblado, Agua caliente','img/habitaciones/h5.jpg','disponible','2026-11-17 00:25:22','2026-11-17 00:25:24',28,2,'propio',550.00);
INSERT INTO `habitaciones` VALUES (6,3,'Habitación compartida en Wanchaq','Habitación para dos estudiantes, cada uno con su cama y escritorio. Zona segura, cerca del Mercado de Wanchaq y con tiendas alrededor.','Wanchaq','Jr. La Convención B-17','UNSAAC',-13.5274600,-71.9635100,220.00,'compartida','Agua y luz, Wifi, Lavandería','img/habitaciones/h6.jpg','disponible','2026-11-17 00:25:22','2026-11-17 00:25:24',20,1,'compartido',220.00);
INSERT INTO `habitaciones` VALUES (7,2,'Habitación amplia en el centro','Habitación grande en casona del barrio de San Cristóbal, con ventana a la calle y mucha luz natural. A cinco cuadras de la Plaza de Armas.','Cusco','Calle Pumacurco 490, San Cristóbal','UNSAAC',-13.5132800,-71.9780400,400.00,'individual','Agua y luz, Wifi, Agua caliente','img/habitaciones/h7.jpg','disponible','2026-11-17 00:25:22','2026-11-17 00:25:24',18,2,'compartido',400.00);
INSERT INTO `habitaciones` VALUES (8,3,'Cuarto para estudiante – Santiago','Cuarto con cama de plaza y media y ropero, frente al Hospital Antonio Lorena. Pasan varias líneas de buses hacia la Av. de la Cultura.','Santiago','Av. Antonio Lorena 780, Dignidad Nacional','UNSAAC',-13.5256400,-71.9882000,270.00,'individual','Agua y luz, Lavandería','img/habitaciones/h8.jpg','disponible','2026-11-17 00:25:22','2026-11-17 00:25:24',11,2,'compartido',270.00);
INSERT INTO `habitaciones` VALUES (9,4,'Cuarto con vista – San Jerónimo','Cuarto en tercer piso con vista a los cerros, cerca de la plaza de San Jerónimo. Buses directos a la U. Andina y a la U. Continental.','San Jerónimo','Calle Clorinda Matto de Turner 210','U. Andina',-13.5415800,-71.8838000,280.00,'individual','Agua y luz, Wifi','img/habitaciones/h9.jpg','disponible','2026-11-17 00:25:22','2026-11-17 00:25:24',12,3,'compartido',280.00);
INSERT INTO `habitaciones` VALUES (10,14,'Cuarto amplio a solo S/ 150 en el centro','Cuarto grande, quedan pocos. Para separarlo deposita 300 soles al número de cuenta que te paso por mensaje.','Cusco','Mesón de la Estrella 175','UNSAAC',-13.5198000,-71.9802000,150.00,'individual','Agua y luz, Wifi','img/habitaciones/h10.jpg','disponible','2026-11-17 00:25:22','2026-11-17 00:25:24',NULL,NULL,NULL,NULL);
INSERT INTO `habitaciones` VALUES (11,15,'Cuarto con escritorio frente al Real Plaza','Cuarto amoblado en tercer piso, con escritorio grande y buena señal de wifi. A cuatro cuadras de la UNSAAC y frente al Real Plaza.','Cusco','Av. Collasuyo 912, Urb. Manuel Prado','UNSAAC',-13.5205400,-71.9552700,350.00,'individual','Agua y luz, Wifi, Amoblado, Escritorio','img/habitaciones/galeria/dormitorio-03.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',13,3,'compartido',350.00);
INSERT INTO `habitaciones` VALUES (12,15,'Habitación en Mariscal Gamarra','Habitación en casa familiar al lado del Parque del Magisterio. Barrio tranquilo, ideal para estudiar. La UNSAAC queda a diez minutos caminando.','Cusco','Parque Magisterio 21, Urb. Mariscal Gamarra','UNSAAC',-13.5211200,-71.9623000,300.00,'individual','Agua y luz, Wifi, Agua caliente','img/habitaciones/galeria/dormitorio-12.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',12,2,'compartido',300.00);
INSERT INTO `habitaciones` VALUES (13,16,'Cuarto cerca de la U. Continental – Angostura','Cuarto a cinco minutos caminando del campus de la U. Continental en Angostura. Sin perder tiempo en el tráfico de la Vía Expresa.','Saylla','Sector Angostura, Mz. C Lt. 8','U. Continental',-13.5551700,-71.8558700,300.00,'individual','Agua y luz, Wifi, Agua caliente','img/habitaciones/galeria/dormitorio-01.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',14,2,'compartido',300.00);
INSERT INTO `habitaciones` VALUES (14,16,'Minidepartamento en Saylla','Minidepartamento nuevo con cocina, baño propio y espacio para bicicleta o moto. A cinco minutos de la U. Continental.','Saylla','Carretera Cusco–Urcos km 10','U. Continental',-13.5565700,-71.8548700,520.00,'minidepartamento','Agua y luz, Wifi, Baño propio, Cocina, Amoblado, Agua caliente, Cochera','img/habitaciones/galeria/dormitorio-04.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',30,1,'propio',520.00);
INSERT INTO `habitaciones` VALUES (15,16,'Habitación en Oropesa Huaylla','Cuarto sencillo en casa nueva, a unos diez minutos caminando de la U. Continental. Hay tiendas y restaurante de menú en la misma cuadra.','San Jerónimo','Asoc. Oropesa Huaylla Mz. F Lt. 3','U. Continental',-13.5515400,-71.8584000,260.00,'individual','Agua y luz, Wifi','img/habitaciones/galeria/dormitorio-06.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',11,2,'compartido',260.00);
INSERT INTO `habitaciones` VALUES (16,17,'Cuarto en Larapa a pasos de la U. Andina','Cuarto amoblado a dos cuadras del campus Larapa de la U. Andina. Casa con portón eléctrico y patio.','San Jerónimo','Av. Villa del Carmen 340, La Arboleda','U. Andina',-13.5375400,-71.9020900,330.00,'individual','Agua y luz, Wifi, Amoblado, Agua caliente','img/habitaciones/galeria/dormitorio-07.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',13,2,'compartido',330.00);
INSERT INTO `habitaciones` VALUES (17,17,'Habitación compartida en El Tingo','Habitación para dos estudiantes de la U. Andina. Cada uno tiene su cama, escritorio y repisa. Lavadora de uso común.','San Jerónimo','Calle Manantial 118, El Tingo','U. Andina',-13.5392700,-71.9022300,200.00,'compartida','Agua y luz, Wifi, Lavandería','img/habitaciones/galeria/dormitorio-10.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',18,1,'compartido',200.00);
INSERT INTO `habitaciones` VALUES (18,17,'Cuarto con baño propio en Larapita','Cuarto con baño propio en edificio de tres pisos, a cinco minutos del campus Larapa. Agua caliente todo el día.','San Jerónimo','Av. Collasuyo 1520, Urb. Larapita','U. Andina',-13.5354400,-71.9022900,420.00,'individual','Agua y luz, Wifi, Baño propio, Agua caliente','img/habitaciones/galeria/dormitorio-05.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',15,3,'propio',420.00);
INSERT INTO `habitaciones` VALUES (19,4,'Cuarto cerca del Instituto Túpac Amaru','Cuarto en casa familiar a cinco minutos del Instituto Túpac Amaru. Zona residencial, con paraderos sobre la Av. de la Cultura.','San Sebastián','Jr. Las Magnolias 205, Señor de Exaltación','IST Túpac Amaru',-13.5354900,-71.9263500,260.00,'individual','Agua y luz, Wifi','img/habitaciones/galeria/dormitorio-08.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',11,2,'compartido',260.00);
INSERT INTO `habitaciones` VALUES (20,3,'Cuarto económico en Ayarmaca','Cuarto sencillo en primer piso, con cama y mesa. Buena opción si buscas pagar poco y estudias en San Sebastián.','San Sebastián','Calle Mariscal Cáceres 77, Ayarmaca','IST Túpac Amaru',-13.5363500,-71.9368000,230.00,'individual','Agua y luz','img/habitaciones/galeria/dormitorio-09.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',10,1,'compartido',230.00);
INSERT INTO `habitaciones` VALUES (21,2,'Habitación con vista en San Blas','Habitación en casona del barrio de San Blas, con vista a los techos del centro histórico. Calle peatonal y tranquila.','Cusco','Cuesta de San Blas 318','UNSAAC',-13.5148000,-71.9750000,420.00,'individual','Agua y luz, Wifi, Agua caliente, Amoblado','img/habitaciones/galeria/dormitorio-14.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',15,2,'compartido',420.00);
INSERT INTO `habitaciones` VALUES (22,15,'Cuarto en Wanchaq cerca del mercado','Cuarto en Wanchaq a una cuadra de la Av. de la Cultura. Mercado, farmacias y bancos alrededor.','Wanchaq','Av. Huayruropata 1205','UNSAAC',-13.5247200,-71.9645200,300.00,'individual','Agua y luz, Wifi, Cocina','img/habitaciones/galeria/dormitorio-11.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',12,2,'compartido',300.00);
INSERT INTO `habitaciones` VALUES (23,15,'Minidepartamento en Av. Garcilaso','Minidepartamento completo en cuarto piso: dormitorio, kitchenette y baño propio. Ideal para compartir entre hermanos.','Wanchaq','Av. Garcilaso 615','UNSAAC',-13.5219300,-71.9709200,600.00,'minidepartamento','Agua y luz, Wifi, Baño propio, Cocina, Amoblado, Agua caliente, Cable','img/habitaciones/galeria/dormitorio-13.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',32,4,'propio',600.00);
INSERT INTO `habitaciones` VALUES (24,3,'Cuarto junto a la Plaza Belén','Cuarto frente a la Plaza Belén, en Santiago. Precio cómodo y a diez minutos en bus del centro.','Santiago','Plaza Belén 14, Urb. Roccopata','UNSAAC',-13.5267500,-71.9808300,240.00,'individual','Agua y luz, Wifi','img/habitaciones/galeria/dormitorio-15.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',10,2,'compartido',240.00);
INSERT INTO `habitaciones` VALUES (25,17,'Cuarto cerca del Mercado Vinocanchón','Cuarto a tres cuadras del Mercado Vinocanchón. Buses a la U. Andina y a la U. Continental en la misma avenida.','San Jerónimo','Calle Arica 330, Pata Pata','U. Andina',-13.5445400,-71.8830300,250.00,'individual','Agua y luz, Wifi, Lavandería','img/habitaciones/galeria/dormitorio-16.jpg','disponible','2026-11-17 00:25:24','2026-11-17 00:25:24',11,2,'compartido',250.00);
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

INSERT INTO `mensajes` VALUES (1,1,11,'Hola señora Rosa, ¿el cuarto tiene escritorio?',1,'2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `mensajes` VALUES (2,1,2,'Hola Ana, sí tiene escritorio y una silla. Te espero el día de la visita.',1,'2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `mensajes` VALUES (3,1,11,'Perfecto, muchas gracias.',0,'2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `mensajes` VALUES (4,2,10,'Buenas tardes, ¿qué días empezaría?',1,'2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `mensajes` VALUES (5,2,6,'Hola Luis, puedes empezar este martes a las 2 pm en el local de Santiago.',0,'2026-11-17 00:25:22','2026-11-17 00:25:22');
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` VALUES (1,'2026_11_01_000001_create_usuarios_table',1);
INSERT INTO `migrations` VALUES (2,'2026_11_01_000002_create_habitaciones_table',1);
INSERT INTO `migrations` VALUES (3,'2026_11_01_000003_create_trabajos_table',1);
INSERT INTO `migrations` VALUES (4,'2026_11_15_000001_create_modulos_finales',1);
INSERT INTO `migrations` VALUES (5,'2026_11_16_000001_create_contactos_y_sesiones',1);
INSERT INTO `migrations` VALUES (6,'2026_11_17_000001_create_eventos_seguridad',1);
INSERT INTO `migrations` VALUES (7,'2026_11_18_000001_create_identidad',1);
INSERT INTO `migrations` VALUES (8,'2026_11_19_000001_create_galeria_y_detalles',1);
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

INSERT INTO `notifications` VALUES ('0b7ae0f2-8aa4-47b1-862f-487f38af81ad','App\\Notifications\\Aviso','App\\Models\\Usuario',10,'{\"texto\":\"Minimarket Wayra acept\\u00f3 tu postulaci\\u00f3n a \\\"Repartidor en bicicleta\\\"\",\"url\":\"\\/estudiante\",\"icono\":\"briefcase\"}',NULL,'2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `notifications` VALUES ('2ba976d5-3d5b-435b-8616-c2b9e599732e','App\\Notifications\\Aviso','App\\Models\\Usuario',3,'{\"texto\":\"Ana Quispe Ccasa quiere visitar \\\"Habitaci\\u00f3n compartida en Wanchaq\\\"\",\"url\":\"\\/arrendador\\/solicitudes\",\"icono\":\"house-door\"}',NULL,'2026-11-17 00:25:22','2026-11-17 00:25:22');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `postulaciones` VALUES (1,1,10,'Tengo bicicleta propia y conozco bien Santiago.','aceptada','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `postulaciones` VALUES (2,6,10,'Tengo inglés básico.','pendiente','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `postulaciones` VALUES (3,5,11,'Estoy en sexto ciclo de Ingeniería de Sistemas.','pendiente','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `postulaciones` VALUES (4,4,13,NULL,'aceptada','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `postulaciones` VALUES (5,1,12,NULL,'rechazada','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `postulaciones` VALUES (6,13,21,'Ingresé a Ingeniería Civil por examen y ya enseñé a mis primos.','aceptada','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `postulaciones` VALUES (7,8,22,'Trabajé en la cafetería de mi tío en Urubamba.','pendiente','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `postulaciones` VALUES (8,11,23,'Tengo inglés intermedio y estudio de día.','pendiente','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `postulaciones` VALUES (9,18,12,NULL,'pendiente','2026-11-17 00:25:24','2026-11-17 00:25:24');
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

INSERT INTO `reportes` VALUES (1,13,10,NULL,'Pide adelanto sin visita','Me pidió depositar 300 soles para separar el cuarto sin dejarme verlo.','pendiente','2026-11-17 00:25:22','2026-11-17 00:25:22');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `resenas` VALUES (1,1,10,5,'La habitación es igual que en las fotos. La señora Rosa es muy amable y la zona es tranquila.','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `resenas` VALUES (2,1,12,4,'Buena habitación y cerca de la universidad. El wifi a veces falla en las noches.','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `resenas` VALUES (3,3,13,5,'El baño propio y el agua caliente valen la pena. Muy seguro el edificio.','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `resenas` VALUES (4,2,12,3,'Es económico pero el baño compartido es pequeño. Para el precio está bien.','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `resenas` VALUES (5,13,21,5,'Llego a clases en cinco minutos. El señor Fredy es muy atento y el agua caliente funciona bien.','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `resenas` VALUES (6,16,22,5,'Muy cerca de la universidad y la casa es segura. Las fotos son reales.','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `resenas` VALUES (7,11,23,4,'Buen escritorio y buen wifi. Por las noches hay algo de ruido de la avenida.','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `resenas` VALUES (8,17,22,4,'Compartí con una compañera de la U. Andina y nos fue bien. La lavadora ayuda mucho.','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `resenas` VALUES (9,14,21,5,'Tiene todo lo necesario y está al lado de la Continental. Vale lo que cuesta.','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `resenas` VALUES (10,21,23,5,'La vista es increíble y el barrio es tranquilo. Hay que subir escaleras, eso sí.','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `resenas` VALUES (11,12,11,4,'Zona tranquila y cerca de la UNSAAC. La familia es respetuosa.','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `resenas` VALUES (12,19,13,3,'Está bien para el precio. El baño compartido se llena por las mañanas.','2026-11-17 00:25:24','2026-11-17 00:25:24');
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `solicitudes` VALUES (1,4,11,'2026-11-18','Hola, soy de Abancay y estudio en la UNSAAC. ¿Puedo ir el sábado?','aceptada','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `solicitudes` VALUES (2,6,11,'2026-11-20','¿La habitación compartida es solo para mujeres?','pendiente','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `solicitudes` VALUES (3,1,10,'2026-11-10','Quisiera verla antes de mudarme.','aceptada','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `solicitudes` VALUES (4,1,12,'2026-11-06','Hola, estudio en la Continental.','aceptada','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `solicitudes` VALUES (5,3,13,'2026-11-08','¿Puedo ir por la tarde?','aceptada','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `solicitudes` VALUES (6,2,12,'2026-11-04','Me interesa, ¿sigue disponible?','aceptada','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `solicitudes` VALUES (7,7,11,'2026-11-13','Hola, ¿puedo verla el lunes?','aceptada','2026-11-17 00:25:22','2026-11-17 00:25:22');
INSERT INTO `solicitudes` VALUES (8,13,21,'2026-11-07','Me gustaría ver el cuarto antes de decidir.','aceptada','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `solicitudes` VALUES (9,16,22,'2026-11-06','Me gustaría ver el cuarto antes de decidir.','aceptada','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `solicitudes` VALUES (10,11,23,'2026-10-28','Me gustaría ver el cuarto antes de decidir.','aceptada','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `solicitudes` VALUES (11,17,22,'2026-11-08','Me gustaría ver el cuarto antes de decidir.','aceptada','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `solicitudes` VALUES (12,14,21,'2026-11-05','Me gustaría ver el cuarto antes de decidir.','aceptada','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `solicitudes` VALUES (13,21,23,'2026-11-02','Me gustaría ver el cuarto antes de decidir.','aceptada','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `solicitudes` VALUES (14,12,11,'2026-10-31','Me gustaría ver el cuarto antes de decidir.','aceptada','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `solicitudes` VALUES (15,19,13,'2026-11-07','Me gustaría ver el cuarto antes de decidir.','aceptada','2026-11-17 00:25:24','2026-11-17 00:25:24');
INSERT INTO `solicitudes` VALUES (16,23,21,'2026-11-19','¿Se puede compartir entre dos hermanos?','pendiente','2026-11-17 00:25:24','2026-11-17 00:25:24');
DROP TABLE IF EXISTS `trabajo_horarios`;
CREATE TABLE `trabajo_horarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trabajo_id` bigint(20) unsigned NOT NULL,
  `dia` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
  `turno` enum('manana','tarde','noche') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trabajo_horarios_trabajo_id_dia_turno_unique` (`trabajo_id`,`dia`,`turno`),
  CONSTRAINT `trabajo_horarios_trabajo_id_foreign` FOREIGN KEY (`trabajo_id`) REFERENCES `trabajos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
INSERT INTO `trabajo_horarios` VALUES (24,8,'lunes','tarde');
INSERT INTO `trabajo_horarios` VALUES (25,8,'miercoles','tarde');
INSERT INTO `trabajo_horarios` VALUES (26,8,'viernes','tarde');
INSERT INTO `trabajo_horarios` VALUES (27,8,'sabado','manana');
INSERT INTO `trabajo_horarios` VALUES (28,9,'viernes','noche');
INSERT INTO `trabajo_horarios` VALUES (29,9,'sabado','noche');
INSERT INTO `trabajo_horarios` VALUES (30,10,'martes','manana');
INSERT INTO `trabajo_horarios` VALUES (31,10,'jueves','manana');
INSERT INTO `trabajo_horarios` VALUES (32,10,'sabado','manana');
INSERT INTO `trabajo_horarios` VALUES (33,11,'lunes','noche');
INSERT INTO `trabajo_horarios` VALUES (34,11,'miercoles','noche');
INSERT INTO `trabajo_horarios` VALUES (35,11,'viernes','noche');
INSERT INTO `trabajo_horarios` VALUES (36,12,'sabado','manana');
INSERT INTO `trabajo_horarios` VALUES (37,12,'domingo','manana');
INSERT INTO `trabajo_horarios` VALUES (38,13,'martes','tarde');
INSERT INTO `trabajo_horarios` VALUES (39,13,'jueves','tarde');
INSERT INTO `trabajo_horarios` VALUES (40,13,'sabado','manana');
INSERT INTO `trabajo_horarios` VALUES (41,14,'lunes','tarde');
INSERT INTO `trabajo_horarios` VALUES (42,14,'miercoles','tarde');
INSERT INTO `trabajo_horarios` VALUES (43,15,'sabado','tarde');
INSERT INTO `trabajo_horarios` VALUES (44,15,'domingo','tarde');
INSERT INTO `trabajo_horarios` VALUES (45,16,'sabado','manana');
INSERT INTO `trabajo_horarios` VALUES (46,16,'sabado','tarde');
INSERT INTO `trabajo_horarios` VALUES (47,16,'domingo','manana');
INSERT INTO `trabajo_horarios` VALUES (48,17,'lunes','tarde');
INSERT INTO `trabajo_horarios` VALUES (49,17,'martes','tarde');
INSERT INTO `trabajo_horarios` VALUES (50,17,'jueves','tarde');
INSERT INTO `trabajo_horarios` VALUES (51,18,'sabado','manana');
INSERT INTO `trabajo_horarios` VALUES (52,18,'domingo','manana');
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
  `direccion` varchar(150) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `requisitos` text DEFAULT NULL,
  `vacantes` tinyint(3) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `trabajos_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `trabajos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `trabajos` VALUES (1,6,'Repartidor en bicicleta','Minimarket Wayra','Reparto de pedidos en la zona de Santiago. Se da la bicicleta.','Santiago','medio tiempo',40.00,'dia','activa','2026-11-17 00:25:22','2026-11-17 00:25:24','Calle Los Ángeles 1265, Urb. Roccopata',-13.5262800,-71.9848400,'Tener bicicleta o saber manejarla\nConocer las calles de Santiago\nMayor de 18 años',2);
INSERT INTO `trabajos` VALUES (2,7,'Soporte técnico sábados','Andes Tech SAC','Mantenimiento de computadoras e instalación de programas.','Wanchaq','por horas',70.00,'dia','activa','2026-11-17 00:25:22','2026-11-17 00:25:24','Av. Tomasa Tito Condemayta 718',-13.5241973,-71.9647209,'Estudiar Ingeniería de Sistemas o afines\nSaber instalar Windows y armar computadoras',1);
INSERT INTO `trabajos` VALUES (3,8,'Ayudante de cocina (mañanas)','Restaurante Inti Raymi','Apoyo en la preparación de desayunos y almuerzos.','Cusco','medio tiempo',50.00,'dia','activa','2026-11-17 00:25:22','2026-11-17 00:25:24','Calle Procuradores 344',-13.5155052,-71.9794839,'Carné de sanidad (te ayudamos a sacarlo)\nPuntualidad',2);
INSERT INTO `trabajos` VALUES (4,6,'Atención en minimarket (noches)','Minimarket Wayra','Atención al cliente y caja en turno de noche.','San Sebastián','medio tiempo',45.00,'dia','activa','2026-11-17 00:25:22','2026-11-17 00:25:24','Av. de la Cultura s/n, frente a la plaza de San Sebastián',-13.5300534,-71.9373475,'Facilidad para atender al público\nManejo básico de caja',1);
INSERT INTO `trabajos` VALUES (5,7,'Practicante de sistemas','Andes Tech SAC','Apoyo en desarrollo web y soporte a usuarios. Para estudiantes de Ingeniería de Sistemas.','Wanchaq','practicas',1025.00,'mes','activa','2026-11-17 00:25:22','2026-11-17 00:25:24','Av. Tomasa Tito Condemayta 718',-13.5241973,-71.9647209,'Estudiante de sexto ciclo en adelante\nConocer HTML, CSS y algo de PHP o JavaScript',1);
INSERT INTO `trabajos` VALUES (6,8,'Mozo/a fin de semana','Restaurante Inti Raymi','Atención en mesas los fines de semana. Se valora inglés básico.','Cusco','medio tiempo',60.00,'dia','activa','2026-11-17 00:25:22','2026-11-17 00:25:24','Calle Procuradores 344',-13.5155052,-71.9794839,'Inglés básico para atender turistas\nBuena presencia y trato amable',3);
INSERT INTO `trabajos` VALUES (7,9,'Guía de apoyo turístico','Qhapaq Tours','Acompañar a grupos de turistas en el centro histórico.','Cusco','por horas',80.00,'dia','activa','2026-11-17 00:25:22','2026-11-17 00:25:24','Plaza San Blas 102',-13.5151764,-71.9742158,'Estudiante de Turismo\nInglés intermedio',2);
INSERT INTO `trabajos` VALUES (8,18,'Barista medio tiempo','Café Kuntur','Preparar cafés, jugos y postres y atender la barra. Te enseñamos a usar la máquina de espresso.','Cusco','medio tiempo',45.00,'dia','activa','2026-11-17 00:25:24','2026-11-17 00:25:24','Av. El Sol 520',-13.5227515,-71.9745808,'Ganas de aprender\nTrato amable con el público',2);
INSERT INTO `trabajos` VALUES (9,18,'Ayudante de barra (noches de fin de semana)','Café Kuntur','Apoyo en barra y limpieza los viernes y sábados por la noche.','Cusco','por horas',12.00,'hora','activa','2026-11-17 00:25:24','2026-11-17 00:25:24','Av. El Sol 520',-13.5227515,-71.9745808,'Mayor de 18 años\nDisponibilidad viernes y sábado',2);
INSERT INTO `trabajos` VALUES (10,18,'Ayudante en juguería del mercado','Café Kuntur','Preparar jugos y atender a los clientes del puesto en el Mercado de Wanchaq.','Wanchaq','medio tiempo',35.00,'dia','activa','2026-11-17 00:25:24','2026-11-17 00:25:24','Mercado de Wanchaq, puesto 48',-13.5312589,-71.9603152,'Puntualidad\nCarné de sanidad',1);
INSERT INTO `trabajos` VALUES (11,19,'Recepcionista de noche en hostal','Hostal Ñusta Wasi','Recibir a los huéspedes, hacer check-in y atender el teléfono. Hay tiempo para estudiar en el turno.','Cusco','medio tiempo',50.00,'dia','activa','2026-11-17 00:25:24','2026-11-17 00:25:24','Cuesta de San Blas 310',-13.5150500,-71.9748000,'Inglés básico o intermedio\nResponsabilidad',1);
INSERT INTO `trabajos` VALUES (12,19,'Apoyo en desayunos (fines de semana)','Hostal Ñusta Wasi','Servir desayunos y ordenar el comedor del hostal los sábados y domingos.','Cusco','medio tiempo',40.00,'dia','activa','2026-11-17 00:25:24','2026-11-17 00:25:24','Cuesta de San Blas 310',-13.5150500,-71.9748000,'Puntualidad\nOrden y limpieza',2);
INSERT INTO `trabajos` VALUES (13,20,'Profesor/a de matemática para preuniversitarios','Academia Saqsay','Dictar clases de álgebra y aritmética a grupos de 15 alumnos que se preparan para el examen de admisión.','Wanchaq','por horas',25.00,'hora','activa','2026-11-17 00:25:24','2026-11-17 00:25:24','Av. Pachacútec 615',-13.5244740,-71.9704044,'Estudiante de ingeniería, matemática o educación\nHaber ingresado a la universidad por examen',3);
INSERT INTO `trabajos` VALUES (14,20,'Tutor/a de inglés para escolares','Academia Saqsay','Reforzar inglés a escolares de secundaria en grupos pequeños.','Wanchaq','por horas',20.00,'hora','activa','2026-11-17 00:25:24','2026-11-17 00:25:24','Av. Pachacútec 615',-13.5244740,-71.9704044,'Inglés intermedio o avanzado\nPaciencia con adolescentes',2);
INSERT INTO `trabajos` VALUES (15,20,'Promotor/a de matrícula en Real Plaza','Academia Saqsay','Informar sobre los ciclos de la academia en un módulo dentro del centro comercial.','Cusco','por horas',50.00,'dia','activa','2026-11-17 00:25:24','2026-11-17 00:25:24','Real Plaza Cusco, Av. Collasuyo 2964',-13.5234656,-71.9502285,'Facilidad de palabra\nDisponibilidad los domingos',4);
INSERT INTO `trabajos` VALUES (16,6,'Cajero/a fin de semana en San Jerónimo','Minimarket Wayra','Atención en caja y reposición de productos en nuestro local de San Jerónimo.','San Jerónimo','medio tiempo',45.00,'dia','activa','2026-11-17 00:25:24','2026-11-17 00:25:24','Calle Agricultura 120, frente al Mercado Vinocanchón',-13.5435425,-71.8877752,'Manejo básico de caja\nVivir cerca de San Jerónimo es un plus',1);
INSERT INTO `trabajos` VALUES (17,7,'Community manager (medio tiempo)','Andes Tech SAC','Manejar las redes sociales de nuestros clientes: publicaciones, diseño básico y respuestas a mensajes.','Wanchaq','medio tiempo',900.00,'mes','activa','2026-11-17 00:25:24','2026-11-17 00:25:24','Av. Tomasa Tito Condemayta 718',-13.5241973,-71.9647209,'Estudiante de Comunicación, Marketing o Diseño\nManejo de Canva',1);
INSERT INTO `trabajos` VALUES (18,7,'Encuestador/a para estudio de mercado','Andes Tech SAC','Aplicar encuestas en celular a vecinos de San Sebastián durante dos fines de semana.','San Sebastián','por horas',50.00,'dia','activa','2026-11-17 00:25:24','2026-11-17 00:25:24','Plaza de Armas de San Sebastián',-13.5300534,-71.9373475,'Celular con datos\nTrato amable',6);
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
  `email_verificado_at` timestamp NULL DEFAULT NULL,
  `codigo_correo` varchar(255) DEFAULT NULL,
  `codigo_correo_vence` timestamp NULL DEFAULT NULL,
  `correo_institucional` varchar(100) DEFAULT NULL,
  `dos_pasos_activo` tinyint(1) NOT NULL DEFAULT 0,
  `dos_pasos_secreto` text DEFAULT NULL,
  `dos_pasos_codigos` text DEFAULT NULL,
  `dos_pasos_ultimo` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_email_unique` (`email`),
  UNIQUE KEY `usuarios_dni_unique` (`dni`),
  UNIQUE KEY `usuarios_correo_institucional_unique` (`correo_institucional`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` VALUES (1,'Administrador TAMPU','admin@tampu.pe','70000001',NULL,NULL,NULL,'admin','no_aplica',0,NULL,'$2y$12$JVAAxb7wpoHoaVSh9v9JOuyAFrKorIs8q1HKnFVkT.lhNBS8KQeSK',NULL,'2026-11-17 00:25:18','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (2,'Rosa Huamán Quispe','rosa@tampu.pe','41234567','984111222',NULL,NULL,'arrendador','verificado',0,NULL,'$2y$12$YtJ0zXypcvE4n2hVdHYSu.Fa5oTtYQXsOkYN3LT5YxOd8SYXgbAee',NULL,'2026-11-17 00:25:18','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (3,'Julio Ccori Mamani','julio@tampu.pe','42345678','984333444',NULL,NULL,'arrendador','verificado',0,NULL,'$2y$12$EA.gy7Mcc/vTJF.jekyph.vY2zP7oKrfN6XGr9So1rn/SM/uQhjq2',NULL,'2026-11-17 00:25:19','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (4,'Carmen Mamani Huallpa','carmen@tampu.pe','43456789','984555666',NULL,NULL,'arrendador','verificado',0,NULL,'$2y$12$itdrkmzNMk0QcO.GOiBmj.LLXkQlwJ0sHGzuzp2JfQsHy2sNwsDB.',NULL,'2026-11-17 00:25:19','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (5,'Pedro Ccahuana Rojas','pedro@tampu.pe','44567890','984777888',NULL,NULL,'arrendador','pendiente',0,'documentos/demo-pedro.png','$2y$12$XVWyD9aYSIXa3uUitgoXtuqYfvN7Ro1ZGjCJb93YrlcvX2buLTwJe',NULL,'2026-11-17 00:25:19','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (6,'Lucía Ttito Condori','wayra@tampu.pe','45678901','974111222',NULL,NULL,'empleador','verificado',0,NULL,'$2y$12$KmMQ2HxcSZ9zYUTW9tP6Fujn9ygZCxf5l1gVFAlNmC3PxaWJJloZq',NULL,'2026-11-17 00:25:19','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (7,'Marco Paredes Soto','andestech@tampu.pe','46789012','974333444',NULL,NULL,'empleador','verificado',0,NULL,'$2y$12$5UHLL7HZ3q11UqRI4.sOhedJgi.mJGttGO638O8bq/8qQYdvcB6ki',NULL,'2026-11-17 00:25:20','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (8,'Elena Condori Apaza','intiraymi@tampu.pe','47890123','974555666',NULL,NULL,'empleador','verificado',0,NULL,'$2y$12$plP.vv5xSNYAHR8dlFCA4uE6vJ6CFUTk8aW9z/E.iwj5/2rcJ9tza',NULL,'2026-11-17 00:25:20','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (9,'Diego Huallpa Cusi','qhapaq@tampu.pe','48901234','974777888',NULL,NULL,'empleador','pendiente',0,'documentos/demo-qhapaq.png','$2y$12$4yOuWQu0yCnBgzPEmOGhAufGnc.u9Q08uY.pyM18C6.4TvVsbFGkS',NULL,'2026-11-17 00:25:20','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (10,'Luis Mamani Choque','luis@tampu.pe','72345678','951222333','Sicuani',NULL,'estudiante','no_aplica',0,NULL,'$2y$12$XcWMZ/00oNOShAforuSp/OiSl2vswPc6aafoF1oX8i/4z5P0z7gBG',NULL,'2026-11-17 00:25:20','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (11,'Ana Quispe Ccasa','ana@tampu.pe','73456789','951444555','Abancay',NULL,'estudiante','no_aplica',0,NULL,'$2y$12$BD6MKTPcuLBBCKb2A7KEm.8czD1gYvJLKqjRKtTnpKJIf51Ol/IKm',NULL,'2026-11-17 00:25:21','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (12,'Rocío Apaza Layme','rocio@tampu.pe','74567890','951666777','Puno',NULL,'estudiante','no_aplica',0,NULL,'$2y$12$HlCytdGvNHutVv9AknwzyuWkbNj7Qh4hJvpmTQkwuGRllXfG3Rel.',NULL,'2026-11-17 00:25:21','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (13,'Kevin Huamán Cusi','kevin@tampu.pe','75678901','951888999','Quillabamba',NULL,'estudiante','no_aplica',0,NULL,'$2y$12$ZmZIh3384vNPeTSVppiYueE.6jboMSSwpyqsL8Ql/xZMWiWj2j13q',NULL,'2026-11-17 00:25:21','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (14,'Mario Sullca Ttito','mario@tampu.pe','49012345','984999000',NULL,NULL,'arrendador','verificado',0,NULL,'$2y$12$xJG85Qj4V6t.lw0jggq0XuTxsFAv6MGB6UIkYbDBI7AClhqa04pZe',NULL,'2026-11-17 00:25:22','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (15,'Hilda Quispe Ttito','hilda@tampu.pe','41987654','984222111',NULL,NULL,'arrendador','verificado',0,NULL,'$2y$12$NSMqY3L7HhBjUGOmxtU1ce/q1rTBiVepT1cpWAv1c/oTdLhL2Gf6S',NULL,'2026-11-17 00:25:22','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (16,'Fredy Callañaupa Huamán','fredy@tampu.pe','42876543','984444333',NULL,NULL,'arrendador','verificado',0,NULL,'$2y$12$iG.wCUE9smlJzIzRhx2swOvDPpcyLnleGNhrHdaytO5.UlBIjrKaO',NULL,'2026-11-17 00:25:22','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (17,'Norma Álvarez Puma','norma@tampu.pe','43765432','984666555',NULL,NULL,'arrendador','verificado',0,NULL,'$2y$12$qeSU5ANcJUtU65vWKH8cM.J/FT0FK6.ejy2dY2RVdag5chOohE5D6',NULL,'2026-11-17 00:25:22','2026-11-17 00:25:22','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (18,'Raúl Quispe Mayta','kuntur@tampu.pe','45123987','974222111',NULL,NULL,'empleador','verificado',0,NULL,'$2y$12$n2qkmiDcwtOJyy9cY.Z8ZOmfBi457KS84FzOaCgEG9dwsp.RZGzhK',NULL,'2026-11-17 00:25:23','2026-11-17 00:25:23','2026-11-17 00:25:22',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (19,'Patricia Rojas Huamán','nustawasi@tampu.pe','46234876','974444333',NULL,NULL,'empleador','verificado',0,NULL,'$2y$12$Lt0l3yOAeXOxXNQQZhM88OhAUXs31uc1Y/6oJ3xEccvIhA/9Y.VaS',NULL,'2026-11-17 00:25:23','2026-11-17 00:25:23','2026-11-17 00:25:23',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (20,'Sonia Choque Pari','saqsay@tampu.pe','47345765','974666555',NULL,NULL,'empleador','verificado',0,NULL,'$2y$12$suaJKZwTOZVGlFBA2ZLqV.9akUdpaDiRKMRlDFg1lUyAk4a9kxWei',NULL,'2026-11-17 00:25:23','2026-11-17 00:25:23','2026-11-17 00:25:23',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (21,'Yhon Ccama Huanca','yhon@tampu.pe','76789012','952111222','Espinar',NULL,'estudiante','no_aplica',0,NULL,'$2y$12$.CTdC9qY/9AsaR/f1uOcKekZXGY7lKlFXUmsPXCrnggvRZvM/4S5q',NULL,'2026-11-17 00:25:23','2026-11-17 00:25:23','2026-11-17 00:25:23',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (22,'Milagros Ttito Roque','milagros@tampu.pe','77890123','952333444','Urubamba',NULL,'estudiante','no_aplica',0,NULL,'$2y$12$ZlvHArgo.E7n2XTrE6F5cul4mxu89SA4uHsehB0CYjPK9TeiSoYPq',NULL,'2026-11-17 00:25:24','2026-11-17 00:25:24','2026-11-17 00:25:23',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `usuarios` VALUES (23,'Brayan Huillca Soncco','brayan@tampu.pe','78901234','952555666','Andahuaylas',NULL,'estudiante','no_aplica',0,NULL,'$2y$12$4j2FmZRVQ0LSJnypf/TczejtEVXn/IIKa/77Rz8YzTKueHmikF.M.',NULL,'2026-11-17 00:25:24','2026-11-17 00:25:24','2026-11-17 00:25:24',NULL,NULL,NULL,0,NULL,NULL,NULL);


SET FOREIGN_KEY_CHECKS = 1;
