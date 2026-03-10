-- =====================================================
-- SCRIPT DE INSTALACIÓN - SISTEMA DE GESTIÓN SENA
-- Base de datos: cphpmysql
-- Servidor: 127.0.0.1:3306
-- Usuario: root (sin contraseña)
-- =====================================================

CREATE DATABASE IF NOT EXISTS `cphpmysql` DEFAULT CHARACTER SET utf8mb4;
USE `cphpmysql`;

-- =====================================================
-- LIMPIAR TABLAS EXISTENTES
-- =====================================================
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar vistas si existen
DROP VIEW IF EXISTS `vista_asignaciones_completas`;
DROP VIEW IF EXISTS `vista_fichas_completas`;
DROP VIEW IF EXISTS `vista_instructores_estadisticas`;

-- Eliminar todas las tablas
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `aprendices`;
DROP TABLE IF EXISTS `asignaciones`;
DROP TABLE IF EXISTS `notificaciones_instructor`;
DROP TABLE IF EXISTS `notificaciones_coordinador`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `fichas`;
DROP TABLE IF EXISTS `instructores`;
DROP TABLE IF EXISTS `administradores`;
DROP TABLE IF EXISTS `ambientes`;
DROP TABLE IF EXISTS `auditoria_asignaciones`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `experiencias`;
DROP TABLE IF EXISTS `sedes`;
DROP TABLE IF EXISTS `programas`;
DROP TABLE IF EXISTS `competencias`;
DROP TABLE IF EXISTS `transversales`;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- TABLA USUARIOS
-- =====================================================
-- Tabla principal del sistema de autenticación
-- Almacena todos los usuarios del sistema (administradores, coordinadores, instructores)
-- 
-- Campos importantes:
-- - documento: Número de identificación único (CC, CE, TI, PAS)
-- - tipo_documento: Tipo de documento de identidad
-- - direccion: Dirección de residencia del usuario
-- - rol: Define el nivel de acceso (administrador, coordinador, instructor)
-- - activo: Estado del usuario (1=activo, 0=inactivo)
-- - password: Contraseña hasheada con password_hash() usando bcrypt
-- 
-- Índices para optimización:
-- - idx_email: Búsqueda rápida por email (login)
-- - idx_rol: Filtrado por tipo de usuario
-- =====================================================
CREATE TABLE `usuarios` (
  `usuario_id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(200) DEFAULT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `documento` VARCHAR(20) DEFAULT NULL UNIQUE,
  `tipo_documento` ENUM('CC', 'CE', 'TI', 'PAS') DEFAULT 'CC',
  `direccion` VARCHAR(200) DEFAULT NULL,
  `rol` ENUM('administrador', 'instructor', 'coordinador') NOT NULL DEFAULT 'instructor',
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` TIMESTAMP NULL DEFAULT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usuario_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla PASSWORD_RESETS (Recuperación de contraseñas)
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `token` VARCHAR(100) NOT NULL,
  `expira` DATETIME NOT NULL,
  `usado` TINYINT(1) DEFAULT 0,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_token` (`token`),
  INDEX `idx_email` (`email`),
  INDEX `idx_usado` (`usado`),
  CONSTRAINT `fk_password_reset_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`usuario_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla SEDES
CREATE TABLE IF NOT EXISTS `sedes` (
  `sede_id` INT NOT NULL AUTO_INCREMENT,
  `nombre_sede` VARCHAR(100) NOT NULL,
  `direccion` VARCHAR(200) DEFAULT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `ciudad` VARCHAR(50) DEFAULT NULL,
  `departamento` VARCHAR(50) DEFAULT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sede_id`),
  INDEX `idx_ciudad` (`ciudad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ejemplos de datos para sedes
INSERT INTO `sedes` (`nombre_sede`,`direccion`,`telefono`,`ciudad`,`departamento`) VALUES
('Sede Pescadero','Calle 15 #2-30','(6) 5555-0000','Pereira','Risaralda'),
('Sede Calzado','Av. Industrial #10-40','(6) 5555-0001','Pereira','Risaralda');

-- Tabla AMBIENTES
CREATE TABLE IF NOT EXISTS `ambientes` (
  `ambiente_id` INT NOT NULL AUTO_INCREMENT,
  `sede_id` INT NOT NULL,
  `nombre_ambiente` VARCHAR(100) NOT NULL,
  `capacidad` INT DEFAULT 30,
  `tipo` ENUM('Aula', 'Laboratorio', 'Taller', 'Virtual') DEFAULT 'Aula',
  `equipamiento` TEXT DEFAULT NULL,
  `estado` ENUM('Disponible', 'Ocupado', 'Mantenimiento') DEFAULT 'Disponible',
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ambiente_id`),
  INDEX `idx_sede` (`sede_id`),
  INDEX `idx_estado` (`estado`),
  CONSTRAINT `fk_ambiente_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes`(`sede_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ejemplos de datos para ambientes
INSERT INTO `ambientes` (`sede_id`,`nombre_ambiente`,`capacidad`,`tipo`,`equipamiento`,`estado`) VALUES
(1,'Aula 204',25,'Aula','Equipos, Aire acondicionado','Disponible'),
(2,'Laboratorio 1',30,'Laboratorio','Computadores, Switch, Servidores','Disponible'),
(1,'Aula 101',30,'Aula','Proyector, Tablero Digital, 30 Sillas','Disponible'),
(1,'Lab Sistemas 201',25,'Laboratorio','25 Computadores, Proyector','Disponible'),
(1,'Laboratorio 301',28,'Laboratorio','28 Computadores, Switch, Router','Disponible'),
(2,'Aula 305',35,'Aula','Proyector, Sistema de Audio','Disponible'),
(1,'Taller Multimedia',20,'Taller','Equipos de edición, Cámaras','Disponible'),
(2,'Sala de Conferencias',50,'Auditorio','Proyector, Sistema de Audio, Video','Disponible');

-- Tabla PROGRAMAS
CREATE TABLE IF NOT EXISTS `programas` (
  `programa_id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(20) NOT NULL UNIQUE,
  `nombre` VARCHAR(200) NOT NULL,
  `nivel` VARCHAR(50) DEFAULT NULL,
  `duracion_meses` INT DEFAULT NULL,
  `estado` ENUM('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`programa_id`),
  INDEX `idx_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de ejemplo para programas
INSERT INTO `programas` (`codigo`, `nombre`, `nivel`, `duracion_meses`, `estado`) VALUES
('228106', 'Análisis y Desarrollo de Software', 'Tecnólogo', 24, 'Activo'),
('228120', 'Gestión de Redes de Datos', 'Tecnólogo', 24, 'Activo'),
('228122', 'Sistemas', 'Tecnólogo', 24, 'Activo'),
('134201', 'Asistencia Administrativa', 'Técnico', 12, 'Activo'),
('123112', 'Gestión Empresarial', 'Tecnólogo', 24, 'Activo'),
('228130', 'Diseño Gráfico', 'Tecnólogo', 24, 'Activo'),
('228140', 'Multimedia', 'Tecnólogo', 24, 'Activo'),
('134210', 'Contabilidad y Finanzas', 'Técnico', 18, 'Activo');

-- Tabla COMPETENCIAS
CREATE TABLE IF NOT EXISTS `competencias` (
  `competencia_id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(20) NOT NULL UNIQUE,
  `descripcion` TEXT NOT NULL,
  `horas` INT DEFAULT NULL,
  `tipo` VARCHAR(50) DEFAULT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`competencia_id`),
  INDEX `idx_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de ejemplo para competencias
INSERT INTO `competencias` (`codigo`, `descripcion`, `horas`, `tipo`) VALUES
('220501001', 'Programar software de acuerdo con el diseño realizado', 400, 'Específica'),
('220501002', 'Realizar mantenimiento preventivo y predictivo que prolongue el funcionamiento de los equipos de cómputo', 200, 'Específica'),
('220501003', 'Implementar la estructura de la base de datos de acuerdo con el diseño', 300, 'Específica'),
('220501004', 'Desarrollar el sistema que cumpla con los requisitos de la solución informática', 350, 'Específica'),
('220501005', 'Aplicar buenas prácticas de calidad en el proceso de desarrollo de software', 150, 'Específica'),
('240201500', 'Promover la interacción idónea consigo mismo, con los demás y con la naturaleza', 80, 'Transversal'),
('240201501', 'Comprender textos en inglés en forma escrita y auditiva', 180, 'Transversal'),
('240201502', 'Producir textos en inglés en forma escrita y oral', 180, 'Transversal'),
('220501006', 'Implementar seguridad en aplicaciones de acuerdo con un plan de seguridad', 200, 'Específica'),
('220501007', 'Construir el sistema de información que cumpla con los requisitos de la solución informática', 400, 'Específica');

-- Tabla EXPERIENCIAS
CREATE TABLE IF NOT EXISTS `experiencias` (
  `experiencia_id` INT NOT NULL AUTO_INCREMENT,
  `nombre_experiencia` VARCHAR(200) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `duracion_horas` INT DEFAULT NULL,
  `nivel` ENUM('Básico', 'Intermedio', 'Avanzado') DEFAULT 'Básico',
  `area_conocimiento` VARCHAR(100) DEFAULT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`experiencia_id`),
  INDEX `idx_nivel` (`nivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla TRANSVERSALES
CREATE TABLE IF NOT EXISTS `transversales` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nom_trans` VARCHAR(200) NOT NULL,
  `duracion` INT DEFAULT NULL,
  `modalidad` VARCHAR(50) DEFAULT NULL,
  `programa` VARCHAR(200) DEFAULT NULL,
  `objetivo` TEXT DEFAULT NULL,
  `descripcion` TEXT DEFAULT NULL,
  `competencias` TEXT DEFAULT NULL,
  `estado` ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla FICHAS
CREATE TABLE IF NOT EXISTS `fichas` (
  `ficha_id` INT NOT NULL AUTO_INCREMENT,
  `codigo_ficha` VARCHAR(20) NOT NULL UNIQUE,
  `programa` VARCHAR(200) NOT NULL,
  `fecha_inicio` DATE DEFAULT NULL,
  `fecha_fin` DATE DEFAULT NULL,
  `estado` ENUM('Activa', 'Finalizada', 'Suspendida') DEFAULT 'Activa',
  `sede_id` INT DEFAULT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ficha_id`),
  INDEX `idx_programa` (`programa`),
  INDEX `idx_estado` (`estado`),
  CONSTRAINT `fk_ficha_sede` FOREIGN KEY (`sede_id`) REFERENCES `sedes`(`sede_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ejemplos de datos para fichas
INSERT INTO `fichas` (`codigo_ficha`,`programa`,`fecha_inicio`,`fecha_fin`,`estado`,`sede_id`) VALUES
('2504321','ADSO','2024-01-15','2024-12-15','Activa',1),
('2619000','Gestión Empresarial','2024-03-01','2025-03-20','Activa',2),
('2558888','Análisis y Desarrollo de Software','2024-01-15','2025-07-15','Activa',1),
('2558889','Multimedia','2024-02-01','2025-08-01','Activa',1),
('2558890','Contabilidad y Finanzas','2024-03-15','2025-09-15','Activa',2),
('2558891','Gestión de Redes','2024-01-20','2025-07-20','Activa',1);

-- Tabla INSTRUCTORES
CREATE TABLE IF NOT EXISTS `instructores` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `documento` VARCHAR(20) NOT NULL UNIQUE,
  `tipo_documento` ENUM('CC', 'CE', 'TI', 'Pasaporte') DEFAULT 'CC',
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `registro` VARCHAR(50) DEFAULT NULL,
  `especialidad` VARCHAR(100) DEFAULT NULL,
  `fecha_ingreso` DATE DEFAULT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ejemplos de datos para el cuerpo de instructores
INSERT INTO `instructores` (`nombre`,`apellido`,`documento`,`tipo_documento`,`email`,`telefono`,`registro`,`especialidad`,`fecha_ingreso`) VALUES
('José','Vera','1234567890','CC','josevera@gmail.com','+57 300 456 7890','REG-2024-001','Desarrollo de Software','2024-01-15'),
('María','Capacho','1234567891','CC','mariapaulacapachogonzalez@gmail.com','+57 300 123 4567','REG-2024-002','ADSO','2024-02-01'),
('Carlos','Rodríguez','1234567892','CC','carlos.rodriguez@sena.edu.co','+57 300 234 5678','REG-2024-003','Bases de Datos','2024-01-20'),
('Ana','Martínez','1234567893','CC','ana.martinez@sena.edu.co','+57 300 345 6789','REG-2024-004','Redes y Telecomunicaciones','2024-03-01'),
('Luis','García','1234567894','CC','luis.garcia@sena.edu.co','+57 300 456 7891','REG-2024-005','Desarrollo Web','2024-01-10'),
('Sandra','López','1234567895','CC','sandra.lopez@sena.edu.co','+57 300 567 8912','REG-2024-006','Programación Móvil','2024-02-15');



-- Tabla ADMINISTRADORES
CREATE TABLE IF NOT EXISTS `administradores` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT DEFAULT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `documento` VARCHAR(20) NOT NULL UNIQUE,
  `tipo_documento` ENUM('CC', 'CE', 'TI', 'Pasaporte') DEFAULT 'CC',
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `cargo` VARCHAR(100) DEFAULT 'Coordinador',
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email_admin` (`email`),
  CONSTRAINT `fk_admin_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`usuario_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTA: Los datos de administradores se insertan al final del script
-- después de crear los usuarios. Ver línea 520+ aproximadamente


-- Tabla ASIGNACIONES
CREATE TABLE IF NOT EXISTS `asignaciones` (
  `asignacion_id` INT NOT NULL AUTO_INCREMENT,
  `ficha_id` INT NOT NULL,
  `instructor_id` INT NOT NULL,
  `experiencia_id` INT NOT NULL,
  `ambiente_id` INT DEFAULT NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NOT NULL,
  `hora_inicio` TIME DEFAULT NULL,
  `hora_fin` TIME DEFAULT NULL,
  `dias_semana` VARCHAR(50) DEFAULT NULL,
  `estado` ENUM('Programada', 'En Curso', 'Finalizada', 'Cancelada') DEFAULT 'Programada',
  `observaciones` TEXT DEFAULT NULL,
  `horas_semanales` INT DEFAULT 0,
  `semestre` VARCHAR(10) DEFAULT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`asignacion_id`),
  INDEX `idx_ficha` (`ficha_id`),
  INDEX `idx_instructor` (`instructor_id`),
  INDEX `idx_experiencia` (`experiencia_id`),
  CONSTRAINT `fk_asignacion_ficha` FOREIGN KEY (`ficha_id`) REFERENCES `fichas`(`ficha_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_asignacion_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructores`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_asignacion_experiencia` FOREIGN KEY (`experiencia_id`) REFERENCES `experiencias`(`experiencia_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_asignacion_ambiente` FOREIGN KEY (`ambiente_id`) REFERENCES `ambientes`(`ambiente_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTA: Los datos de asignaciones se insertan al final del script
-- después de crear experiencias, fichas, instructores y ambientes
-- Ver línea 600+ aproximadamente

-- =====================================================
-- TABLA EVENTS (Eventos del Calendario)
-- =====================================================
-- Tabla para almacenar eventos personalizados del calendario
-- Complementa la tabla asignaciones con eventos adicionales
CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `user_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla APRENDICES
CREATE TABLE IF NOT EXISTS `aprendices` (
  `aprendiz_id` INT NOT NULL AUTO_INCREMENT,
  `ficha_id` INT NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `documento` VARCHAR(20) NOT NULL UNIQUE,
  `tipo_documento` ENUM('CC', 'TI', 'CE', 'Pasaporte') DEFAULT 'CC',
  `email` VARCHAR(100) DEFAULT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `fecha_nacimiento` DATE DEFAULT NULL,
  `direccion` VARCHAR(200) DEFAULT NULL,
  `estado` ENUM('Activo', 'Retirado', 'Graduado', 'Suspendido') DEFAULT 'Activo',
  `fecha_ingreso` DATE DEFAULT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`aprendiz_id`),
  INDEX `idx_ficha` (`ficha_id`),
  CONSTRAINT `fk_aprendiz_ficha` FOREIGN KEY (`ficha_id`) REFERENCES `fichas`(`ficha_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Ejemplos de datos para aprendices
INSERT INTO `aprendices` (`ficha_id`,`nombre`,`apellido`,`documento`,`tipo_documento`,`email`,`telefono`,`estado`,`fecha_ingreso`) VALUES
(1,'Ana','López','1023456789','CC','ana.lopez@gmail.com','+57 312 345 6789','Activo','2024-01-15'),
(1,'Carlos','Ruiz','1034567890','CC','carlos.ruiz@gmail.com','+57 323 456 7890','Activo','2024-01-15');


CREATE TABLE IF NOT EXISTS `notificaciones_instructor` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `instructor_id` INT NOT NULL,
  `coordinador_id` INT NOT NULL,
  `tipo` VARCHAR(50) DEFAULT 'general',
  `titulo` VARCHAR(255) NOT NULL,
  `mensaje` TEXT NOT NULL,
  `datos_extra` TEXT DEFAULT NULL,
  `leida` TINYINT(1) DEFAULT 0,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_lectura` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_instructor` (`instructor_id`),
  INDEX `idx_coordinador` (`coordinador_id`),
  INDEX `idx_leida` (`leida`),
  CONSTRAINT `fk_notif_inst_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructores`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_inst_coordinador` FOREIGN KEY (`coordinador_id`) REFERENCES `usuarios`(`usuario_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla NOTIFICACIONES_COORDINADOR
CREATE TABLE IF NOT EXISTS `notificaciones_coordinador` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `instructor_id` INT NOT NULL,
  `coordinador_id` INT NOT NULL,
  `instructor_nombre` VARCHAR(200) DEFAULT NULL,
  `tipo` VARCHAR(50) DEFAULT 'general',
  `titulo` VARCHAR(255) NOT NULL,
  `mensaje` TEXT NOT NULL,
  `leida` TINYINT(1) DEFAULT 0,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_instructor` (`instructor_id`),
  INDEX `idx_coordinador` (`coordinador_id`),
  INDEX `idx_leida` (`leida`),
  CONSTRAINT `fk_notif_coord_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructores`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_coord_coordinador` FOREIGN KEY (`coordinador_id`) REFERENCES `usuarios`(`usuario_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTA: Los datos de notificaciones se insertan al final del script
-- después de crear usuarios e instructores. Ver línea 615+ aproximadamente

-- Tabla de auditoría (NUEVA)
CREATE TABLE IF NOT EXISTS `auditoria_asignaciones` (
    `id_auditoria` INT AUTO_INCREMENT PRIMARY KEY,
    `asignacion_id` INT,
    `usuario_que_creo` VARCHAR(100),
    `fecha_registro` DATETIME,
    `detalles` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Triggers de Validación (NUEVO)
DELIMITER //

CREATE TRIGGER `before_asignacion_insert_check`
BEFORE INSERT ON `asignaciones`
FOR EACH ROW
BEGIN
    -- No permitir que ninguna asignación individual supere las 20 horas
    IF NEW.horas_semanales > 20 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Error: No se puede asignar una carga mayor a 20 horas por curso.';
    END IF;
END //

-- TRIGGER: Validar conflicto de instructor
CREATE TRIGGER `before_asignacion_instructor_check`
BEFORE INSERT ON `asignaciones`
FOR EACH ROW
BEGIN
    DECLARE conflict_count INT;
    
    SELECT COUNT(*) INTO conflict_count
    FROM asignaciones
    WHERE instructor_id = NEW.instructor_id
      AND (
          (NEW.hora_inicio BETWEEN hora_inicio AND hora_fin) OR
          (NEW.hora_fin BETWEEN hora_inicio AND hora_fin) OR
          (hora_inicio BETWEEN NEW.hora_inicio AND NEW.hora_fin)
      )
      AND (
          -- Simple check for overlapping days in comma separated string
          NEW.dias_semana LIKE CONCAT('%', dias_semana, '%') OR
          dias_semana LIKE CONCAT('%', NEW.dias_semana, '%')
      )
      AND NEW.fecha_inicio <= fecha_fin
      AND NEW.fecha_fin >= fecha_inicio;

    IF conflict_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: El instructor ya tiene una asignación en este horario y días.';
    END IF;
END //

-- TRIGGER: Validar conflicto de ambiente
CREATE TRIGGER `before_asignacion_ambiente_check`
BEFORE INSERT ON `asignaciones`
FOR EACH ROW
BEGIN
    DECLARE conflict_count INT;
    
    IF NEW.ambiente_id IS NOT NULL THEN
        SELECT COUNT(*) INTO conflict_count
        FROM asignaciones
        WHERE ambiente_id = NEW.ambiente_id
          AND (
              (NEW.hora_inicio BETWEEN hora_inicio AND hora_fin) OR
              (NEW.hora_fin BETWEEN hora_inicio AND hora_fin) OR
              (hora_inicio BETWEEN NEW.hora_inicio AND NEW.hora_fin)
          )
          AND (
              NEW.dias_semana LIKE CONCAT('%', dias_semana, '%') OR
              dias_semana LIKE CONCAT('%', NEW.dias_semana, '%')
          )
          AND NEW.fecha_inicio <= fecha_fin
          AND NEW.fecha_fin >= fecha_inicio;

        IF conflict_count > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: El ambiente ya está ocupado en este horario y días.';
        END IF;
    END IF;
END //

-- TRIGGER: Validar fechas de ficha
CREATE TRIGGER `before_ficha_insert_dates_check`
BEFORE INSERT ON `fichas`
FOR EACH ROW
BEGIN
    IF NEW.fecha_fin <= NEW.fecha_inicio THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: La fecha de fin debe ser posterior a la fecha de inicio.';
    END IF;
END //

-- TRIGGER: Auditoría de cambios en asignaciones
CREATE TRIGGER `after_asignacion_update_audit`
AFTER UPDATE ON `asignaciones`
FOR EACH ROW
BEGIN
    INSERT INTO auditoria_asignaciones (asignacion_id, usuario_que_creo, fecha_registro, detalles)
    VALUES (NEW.asignacion_id, 'SISTEMA', NOW(), 
            CONCAT('Actualización: Cambio de ', OLD.estado, ' a ', NEW.estado, '. Instructor ID: ', NEW.instructor_id));
END //

-- TRIGGER: Auditoría de eliminación en asignaciones
CREATE TRIGGER `after_asignacion_delete_audit`
AFTER DELETE ON `asignaciones`
FOR EACH ROW
BEGIN
    INSERT INTO auditoria_asignaciones (asignacion_id, usuario_que_creo, fecha_registro, detalles)
    VALUES (OLD.asignacion_id, 'SISTEMA', NOW(), 
            CONCAT('Eliminación: Se eliminó la asignación para el instructor ID: ', OLD.instructor_id));
END //

DELIMITER ;



-- Tabla EVENTS (calendario de sesiones)
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `user_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_start_date` (`start_date`),
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ejemplos de eventos del calendario
INSERT INTO `events` (`title`, `start_date`, `end_date`, `user_id`) VALUES
('Reunión de Coordinación', '2024-03-15 10:00:00', '2024-03-15 11:30:00', 3),
('Capacitación Docente', '2024-03-20 14:00:00', '2024-03-20 17:00:00', 3),
('Evaluación de Competencias', '2024-03-25 08:00:00', '2024-03-25 12:00:00', 3),
('Reunión con Aprendices', '2024-03-28 09:00:00', '2024-03-28 10:00:00', 3);

-- =====================================================
-- INSERTAR USUARIOS INICIALES
-- =====================================================
-- Usuarios de prueba con contraseñas hasheadas usando bcrypt
-- 
-- CREDENCIALES:
-- 1. Administrador: admin.sena@sena.edu.co / admin123
-- 2. Coordinador: maria.gonzalez@sena.edu.co / maria123
-- 3. Instructor: josevera@gmail.com / jose123
-- 
-- NOTA: Las contraseñas están hasheadas con password_hash()
-- Hash de admin123: $2y$10$IYswExd8VNupAiQLA6oc9effwWoRPvzsP8HWHeETnWJcNc2W5nfc6
-- Hash de maria123: $2y$10$Mln31hepKlo03XJ0sWD31euhr5YFfiPLXifhJpRFJ3sxpD9gxkvGO
-- Hash de admin123: $2y$10$IYswExd8VNupAiQLA6oc9effwWoRPvzsP8HWHeETnWJcNc2W5nfc6
-- Hash de maria123: $2y$10$Mln31hepKlo03XJ0sWD31euhr5YFfiPLXifhJpRFJ3sxpD9gxkvGO
-- Hash de jose123: $2y$10$diwhQxLV51Y5A2pLF.f6dOYH/M/VzYanv3olVtqbjDhlNY05eAU8e
-- =====================================================

INSERT INTO `usuarios` (`nombre`, `email`, `password`, `telefono`, `documento`, `rol`, `activo`) VALUES
('Administrador Principal', 'spiligr1@gmail.com', '$2y$10$pqQ7LBOav5lloqDTiAkvoebdOHbjwOBGgixehMFl4ZrX2Q89.FZve', '+57 300 999 8888', '1234567890', 'administrador', 1),
('María González', 'maria.gonzalez@sena.edu.co', '$2y$10$Mln31hepKlo03XJ0sWD31euhr5YFfiPLXifhJpRFJ3sxpD9gxkvGO', '+57 300 123 4567', '1098765432', 'coordinador', 1),
('José Vera', 'josevera@gmail.com', '$2y$10$diwhQxLV51Y5A2pLF.f6dOYH/M/VzYanv3olVtqbjDhlNY05eAU8e', '+57 300 456 7890', '1234567891', 'instructor', 1);

-- Verificar usuarios creados
SELECT 'Usuarios creados exitosamente' AS Resultado;
SELECT usuario_id, nombre, email, rol FROM usuarios;

-- =====================================================
-- INSERTAR ADMINISTRADORES
-- =====================================================
-- Vincular usuarios con la tabla administradores
-- IMPORTANTE: Este INSERT se ejecuta DESPUÉS de crear los usuarios
-- para evitar errores de clave foránea
-- =====================================================

INSERT INTO `administradores` (`usuario_id`, `nombre`, `apellido`, `documento`, `email`, `telefono`, `cargo`) VALUES
(1, 'Administrador', 'Principal', '1234567890', 'spiligr1@gmail.com', '+57 300 999 8888', 'Administrador Principal'),
(2, 'María', 'González', '1098765432', 'maria.gonzalez@sena.edu.co', '+57 300 123 4567', 'Coordinador Académico');

-- =====================================================
-- CREDENCIALES DE ACCESO AL SISTEMA
-- =====================================================
-- Email: spiligr1@gmail.com
-- Contraseña: 12345678
-- Rol: Administrador
-- =====================================================


-- =====================================================
-- INSERTAR EXPERIENCIAS (Competencias/Cursos)
-- =====================================================


INSERT INTO `ambientes` (`sede_id`, `nombre_ambiente`, `capacidad`, `tipo`, `equipamiento`, `estado`) VALUES
(1, 'Laboratorio 201', 25, 'Laboratorio', '20 Computadores, Equipos', 'Disponible'),
(1, 'Ambiente 101', 30, 'Aula', 'Sillas, Mesas, Pizarra', 'Disponible'),
(1, 'Aula 305', 35, 'Aula', 'Proyector, Equipo de sonido', 'Disponible'),
(1, 'Taller 102', 20, 'Taller', 'Herramientas, Bancos de trabajo', 'Disponible');


INSERT INTO `experiencias` (`nombre_experiencia`, `descripcion`, `duracion_horas`, `nivel`, `area_conocimiento`) VALUES
('Programación Básica', 'Fundamentos de programación con Python', 80, 'Básico', 'Desarrollo de Software'),
('Bases de Datos', 'Diseño y gestión de bases de datos', 60, 'Intermedio', 'Desarrollo de Software'),
('Desarrollo Web', 'HTML, CSS, JavaScript', 120, 'Intermedio', 'Desarrollo de Software'),
('Redes de Computadores', 'Configuración de redes', 100, 'Avanzado', 'Infraestructura TI'),
('Programación Orientada a Objetos', 'POO con Java y C#', 100, 'Intermedio', 'Desarrollo de Software'),
('Desarrollo Móvil', 'Aplicaciones Android e iOS', 120, 'Avanzado', 'Desarrollo de Software'),
('Seguridad Informática', 'Fundamentos de ciberseguridad', 80, 'Avanzado', 'Infraestructura TI'),
('Análisis de Datos', 'Big Data y Analytics', 90, 'Avanzado', 'Ciencia de Datos'),
('Diseño de Interfaces', 'UX/UI Design', 70, 'Intermedio', 'Diseño Digital'),
('Gestión de Proyectos', 'Metodologías ágiles', 60, 'Intermedio', 'Gestión'),
('Cloud Computing', 'AWS, Azure, Google Cloud', 80, 'Avanzado', 'Infraestructura TI'),
('DevOps', 'CI/CD y automatización', 90, 'Avanzado', 'Desarrollo de Software');

-- Ejemplos de datos para transversales
INSERT INTO `transversales` (`nom_trans`, `duracion`, `modalidad`, `programa`, `objetivo`, `descripcion`, `competencias`, `estado`) VALUES
('Ética Profesional', 40, 'Virtual', 'Todos los programas', 'Desarrollar valores éticos en el ámbito profesional', 'Competencia transversal enfocada en la ética y responsabilidad profesional', 'Ética, Responsabilidad, Valores', 'Activo'),
('Emprendimiento', 60, 'Presencial', 'Todos los programas', 'Fomentar el espíritu emprendedor', 'Desarrollo de habilidades empresariales y emprendimiento', 'Liderazgo, Innovación, Gestión', 'Activo'),
('Comunicación Asertiva', 30, 'Mixta', 'Todos los programas', 'Mejorar habilidades de comunicación', 'Técnicas de comunicación efectiva en el entorno laboral', 'Comunicación, Trabajo en equipo', 'Activo');


INSERT INTO `aprendices` (`ficha_id`, `nombre`, `apellido`, `documento`, `tipo_documento`, `email`, `telefono`, `fecha_nacimiento`, `estado`, `fecha_ingreso`) VALUES
(1, 'Juan', 'Pérez', '1000000001', 'CC', 'juan.perez@aprendiz.sena.edu.co', '3201111111', '2000-05-15', 'Activo', '2024-01-15'),
(1, 'María', 'García', '1000000002', 'CC', 'maria.garcia@aprendiz.sena.edu.co', '3202222222', '2001-08-20', 'Activo', '2024-01-15'),
(2, 'Carlos', 'López', '1000000003', 'CC', 'carlos.lopez@aprendiz.sena.edu.co', '3203333333', '1999-12-10', 'Activo', '2024-02-01');

-- =====================================================
-- INSERTAR ASIGNACIONES
-- =====================================================
-- IMPORTANTE: Este INSERT se ejecuta DESPUÉS de crear:
-- - Fichas (línea 586)
-- - Instructores (línea 226)
-- - Experiencias (línea 573)
-- - Ambientes (línea 129, 559, 566)
-- =====================================================

-- Las asignaciones se crean desde el panel de coordinador/administrador
-- INSERT INTO `asignaciones` (`ficha_id`, `instructor_id`, `experiencia_id`, `ambiente_id`, `fecha_inicio`, `fecha_fin`, `hora_inicio`, `hora_fin`, `dias_semana`, `estado`, `observaciones`) VALUES
-- (1, 1, 1, 1, '2024-02-01', '2024-04-15', '08:00:00', '12:00:00', 'Lunes, Miércoles, Viernes', 'En Curso', 'Descripción de la asignación');

-- =====================================================
-- INSERTAR NOTIFICACIONES
-- =====================================================
-- Las notificaciones se generan automáticamente por el sistema
-- INSERT INTO `notificaciones_instructor` (`instructor_id`, `coordinador_id`, `tipo`, `titulo`, `mensaje`, `leida`, `fecha_creacion`) VALUES
-- (1, 1, 'asignacion', 'Nueva Asignación', 'Mensaje de notificación', 0, NOW());

-- INSERT INTO `notificaciones_coordinador` (`instructor_id`, `coordinador_id`, `instructor_nombre`, `tipo`, `titulo`, `mensaje`, `leida`, `fecha_creacion`) VALUES
-- (1, 1, 'Nombre Instructor', 'cambio_perfil', 'Título', 'Mensaje', 0, NOW());
-- (2, 2, 'María Capacho', 'general', 'Consulta sobre Asignación', 'Consulta sobre disponibilidad de ambientes para próxima semana', 1, DATE_SUB(NOW(), INTERVAL 1 DAY));


SET FOREIGN_KEY_CHECKS = 1;


SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'asignacion_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `events` ADD COLUMN `asignacion_id` INT DEFAULT NULL, ADD INDEX `idx_asignacion_id` (`asignacion_id`);',
  'SELECT "column asignacion_id already exists";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Añadir la clave foránea solo si no existe
SET @fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND CONSTRAINT_NAME = 'fk_event_asignacion'
);
SET @sql2 := IF(@fk_exists = 0,
  'ALTER TABLE `events` ADD CONSTRAINT `fk_event_asignacion` FOREIGN KEY (`asignacion_id`) REFERENCES `asignaciones`(`asignacion_id`) ON DELETE CASCADE;',
  'SELECT "fk_event_asignacion already exists";'
);
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;


SELECT 'VERIFICACIÓN - FICHAS:' as info;
SELECT ficha_id, codigo_ficha, programa, estado FROM fichas;

-- Verificar instructores
SELECT 'VERIFICACIÓN - INSTRUCTORES:' as info;
SELECT id, nombre, apellido, email, especialidad FROM instructores;

-- Verificar experiencias (competencias)
SELECT 'VERIFICACIÓN - EXPERIENCIAS:' as info;
SELECT experiencia_id, nombre_experiencia, duracion_horas, nivel FROM experiencias;

-- Verificar ambientes
SELECT 'VERIFICACIÓN - AMBIENTES:' as info;
SELECT ambiente_id, nombre_ambiente, capacidad, tipo, estado FROM ambientes;

-- Verificar asignaciones
SELECT 'VERIFICACIÓN - ASIGNACIONES:' as info;
SELECT COUNT(*) as total_asignaciones FROM asignaciones;

-- Verificar eventos del calendario
SELECT 'VERIFICACIÓN - EVENTOS CALENDARIO:' as info;
SELECT COUNT(*) as total_eventos FROM events;

-- Verificar administradores
SELECT 'VERIFICACIÓN - ADMINISTRADORES:' as info;
SELECT id, nombre, apellido, email, cargo FROM administradores;

-- Verificar auditoría
SELECT 'VERIFICACIÓN - AUDITORÍA ASIGNACIONES:' as info;
SELECT COUNT(*) as total_auditoria FROM auditoria_asignaciones;

-- Verificar transversales
SELECT 'VERIFICACIÓN - TRANSVERSALES:' as info;
SELECT id, nom_trans, duracion, modalidad, estado FROM transversales;

-- Verificar notificaciones de instructores
SELECT 'VERIFICACIÓN - NOTIFICACIONES INSTRUCTORES:' as info;
SELECT COUNT(*) as total_notificaciones, SUM(leida = 0) as no_leidas FROM notificaciones_instructor;

-- Verificar notificaciones de coordinador
SELECT 'VERIFICACIÓN - NOTIFICACIONES COORDINADOR:' as info;
SELECT COUNT(*) as total_notificaciones, SUM(leida = 0) as no_leidas FROM notificaciones_coordinador;

-- Verificar eventos del calendario
SELECT 'VERIFICACIÓN - EVENTOS CALENDARIO:' as info;
SELECT id, title, DATE_FORMAT(start_date, '%d/%m/%Y %H:%i') as fecha_inicio FROM events LIMIT 5;

-- Verificar sistema de recuperación de contraseñas
SELECT 'VERIFICACIÓN - SISTEMA DE RECUPERACIÓN:' as info;
SELECT 'Tabla password_resets creada correctamente' as estado;
SELECT COUNT(*) as total_tokens FROM password_resets;

-- =====================================================
-- INFORMACIÓN DE RECUPERACIÓN DE CONTRASEÑA
-- =====================================================

-- CREDENCIALES DE PRUEBA:
-- Administrador: admin.sena@sena.edu.co / admin123
-- Coordinador: maria.gonzalez@sena.edu.co / maria123
-- Instructor: josevera@gmail.com / jose123

-- ACCESO AL SISTEMA:
-- URL Principal: http://localhost/Mini-Proyecto/
-- Login: index.php?controlador=Auth&accion=login
-- Registro: index.php?controlador=Auth&accion=registro

-- =====================================================
-- MENSAJE DE ÉXITO
-- =====================================================
SELECT '✅ Base de datos creada exitosamente - Sistema listo para usar' AS Resultado;

-- =====================================================
-- CONSULTA DE VERIFICACIÓN DE USUARIOS
-- =====================================================
SELECT 
    usuario_id AS 'ID',
    nombre AS 'Nombre Completo',
    email AS 'Correo Electrónico',
    rol AS 'Rol',
    CASE 
        WHEN email = 'admin.sena@sena.edu.co' THEN 'admin123'
        WHEN email = 'maria.gonzalez@sena.edu.co' THEN 'maria123'
        WHEN email = 'josevera@gmail.com' THEN 'jose123'
        ELSE 'Contactar administrador'
    END AS 'Contraseña',
    CASE 
        WHEN activo = 1 THEN 'Activo'
        ELSE 'Inactivo'
    END AS 'Estado',
    DATE_FORMAT(fecha_creacion, '%d/%m/%Y %H:%i') AS 'Fecha Creación'
FROM usuarios
ORDER BY usuario_id;
