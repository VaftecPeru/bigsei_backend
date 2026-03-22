-- ============================================================
-- VENDEDOR & TUTOR — Schema Changes
-- Fecha: 2026-03-13
-- ============================================================

-- -----------------------------------------------
-- 1. VENDEDOR: Ampliar tabla vendedor
-- -----------------------------------------------
ALTER TABLE `vendedor`
  ADD COLUMN `comision` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje de comisión por venta',
  ADD COLUMN `zona_ventas` VARCHAR(100) DEFAULT NULL COMMENT 'Zona geográfica asignada',
  ADD COLUMN `cuota_mensual` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Cuota mensual de ventas';

-- -----------------------------------------------
-- 2. TUTOR: Crear tabla tutor_estudiante
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `tutor_estudiante` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_tutor` INT NOT NULL COMMENT 'FK a persona (usuario con rol tutor)',
  `id_estudiante` INT NOT NULL COMMENT 'FK a persona (estudiante)',
  `id_empresa` INT DEFAULT NULL COMMENT 'FK a empresa',
  `fecha_asignacion` DATE NOT NULL DEFAULT (CURRENT_DATE),
  `estado` VARCHAR(1) NOT NULL DEFAULT '1' COMMENT '1=activo, 0=inactivo',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_tutor` (`id_tutor`),
  INDEX `idx_estudiante` (`id_estudiante`),
  INDEX `idx_empresa` (`id_empresa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- 3. TUTOR: Crear tabla tutoria_sesion (agenda)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `tutoria_sesion` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_tutor` INT NOT NULL,
  `id_estudiante` INT NOT NULL,
  `id_empresa` INT DEFAULT NULL,
  `fecha` DATE NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fin` TIME DEFAULT NULL,
  `tema` VARCHAR(255) DEFAULT NULL,
  `estado` VARCHAR(20) NOT NULL DEFAULT 'programada' COMMENT 'programada, completada, cancelada',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_tutor_fecha` (`id_tutor`, `fecha`),
  INDEX `idx_estudiante_fecha` (`id_estudiante`, `fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- 4. TUTOR: Crear tabla tutoria_observacion
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `tutoria_observacion` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_sesion` INT UNSIGNED DEFAULT NULL COMMENT 'FK opcional a tutoria_sesion',
  `id_tutor` INT NOT NULL,
  `id_estudiante` INT NOT NULL,
  `id_empresa` INT DEFAULT NULL,
  `observacion` TEXT NOT NULL,
  `tipo` VARCHAR(30) DEFAULT 'general' COMMENT 'general, academico, conductual, emocional',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_tutor_est` (`id_tutor`, `id_estudiante`),
  INDEX `idx_sesion` (`id_sesion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
