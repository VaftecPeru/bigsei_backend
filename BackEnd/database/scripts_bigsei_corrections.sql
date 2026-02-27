-- ==========================================================
-- SCRIPT DE CORRECCIONES BD — BigSei
-- EJECUTAR EN ORDEN. Servidor: MySQL 5.7+ / 8.0+
-- Base de datos: bigsei_bd
-- ==========================================================

USE bigsei_bd;

-- ====================================================
-- #1: Convertir tablas a utf8mb4
--     (soporte para ñ, tildes, emojis)
-- ====================================================
ALTER DATABASE bigsei_bd CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tablas verificadas en las migraciones del proyecto
ALTER TABLE usuario                   CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE usuario_rol               CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE rol                       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE carrera                   CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE carrera_curso             CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE carrera_estudiantes       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE curso                     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE curso_clases              CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE curso_docentes            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE curso_estudiantes         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE curso_evaluaciones        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE curso_horario             CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE curso_horario_estudiantes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE curso_asistencia          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE curso_tipo                CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE matricula                 CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE matricula_cursos          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE matricula_pagos           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE pago                      CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE deudas                    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE evaluacion                CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE evaluaciones_notas        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tramites                  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE certificados              CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE plan_estudio              CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE plan_estudio_ciclos       CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE periodo                   CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE ciclo                     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE ciclo_cursos              CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE seccion                   CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tipo_curso                CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tipo_documento            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE modalidad                 CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE documentos_usuario        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tareas_curso              CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tareas_alumnos            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE libros                    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE reservas                  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE devoluciones              CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE visitas_biblioteca        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE movimientos               CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE pendiente                 CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE actividad_usuario         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE asistencias_docentes      CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE padre_hijo                CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE resenas_curso             CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE course_progress           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE lesson_progress           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE progreso_usuario_contenido CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- ====================================================
-- #2: Corregir contraseñas en texto plano
--     Usuarios IDs 48 y 55
--     ⚠️ ESTO NO SE PUEDE HACER SOLO CON SQL (bcrypt es unidireccional)
--     Ejecutar en Laravel Tinker:
--
--     php artisan tinker
--
--     Luego pegar estas líneas (una por una):
--     DB::table('usuario')->where('id_usuario', 48)->update(['password' => bcrypt('nueva_clave_para_48')]);
--     DB::table('usuario')->where('id_usuario', 55)->update(['password' => bcrypt('nueva_clave_para_55')]);
--
--     Reemplaza 'nueva_clave_para_48' con la contraseña que quieras asignar.
-- ====================================================


-- ====================================================
-- #3: Corregir membresía #2 con fecha incorrecta
-- ====================================================
UPDATE membresia
SET fecha_fin = DATE_ADD(fecha_inicio, INTERVAL 12 MONTH)
WHERE id_membresia = 2
  AND (fecha_fin IS NULL OR fecha_fin <= fecha_inicio);


-- ====================================================
-- #4: Agregar id_empresa a tablas para multi-tenant
--     (solo las tablas que realmente existen en migraciones)
-- ====================================================

-- pago (tabla confirmada en migraciones)
ALTER TABLE pago
  ADD COLUMN id_empresa INT NULL DEFAULT NULL
    COMMENT 'FK multi-tenant — empresa dueña del registro';
ALTER TABLE pago ADD INDEX idx_pago_empresa (id_empresa);

-- evaluaciones_notas (tabla confirmada)
ALTER TABLE evaluaciones_notas
  ADD COLUMN id_empresa INT NULL DEFAULT NULL
    COMMENT 'FK multi-tenant';
ALTER TABLE evaluaciones_notas ADD INDEX idx_evnota_empresa (id_empresa);

-- curso_asistencia (tabla confirmada — "asistencia" en migraciones se llama así)
ALTER TABLE curso_asistencia
  ADD COLUMN id_empresa INT NULL DEFAULT NULL
    COMMENT 'FK multi-tenant';
ALTER TABLE curso_asistencia ADD INDEX idx_asistencia_empresa (id_empresa);

-- certificados (tabla confirmada)
ALTER TABLE certificados
  ADD COLUMN id_empresa INT NULL DEFAULT NULL
    COMMENT 'FK multi-tenant';
ALTER TABLE certificados ADD INDEX idx_certificados_empresa (id_empresa);


-- ====================================================
-- #5: Índices para mejorar rendimiento
-- ====================================================

-- usuario: login por email/username
ALTER TABLE usuario ADD INDEX idx_usuario_email (email);

-- matricula: búsquedas por estudiante
ALTER TABLE matricula ADD INDEX idx_matricula_estudiante (id_estudiante);

-- pago: búsquedas por empresa y fecha
ALTER TABLE pago ADD INDEX idx_pago_empresa_fecha (id_empresa, created_at);

-- evaluaciones_notas: búsquedas por curso
ALTER TABLE evaluaciones_notas ADD INDEX idx_evnota_periodocurso (id_periodocurso);

-- tramites: búsquedas por usuario y estado
ALTER TABLE tramites ADD INDEX idx_tramite_usuario (idUsuario);
ALTER TABLE tramites ADD INDEX idx_tramite_estado (estado);

-- deudas: búsquedas por usuario
ALTER TABLE deudas ADD INDEX idx_deudas_usuario (id_usuario);

-- ====================================================
-- FIN DEL SCRIPT
-- ====================================================
