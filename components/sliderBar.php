<?php

/**
 * ============================================
 * Barra lateral de navegación principal (sliderBar.php)
 * ============================================
 * Este componente muestra la barra lateral con accesos rápidos a las principales funciones del sistema.
 * Las opciones visibles dependen del rol del usuario logueado.
 * Cada botón puede abrir un modal, redirigir a otra página o mostrar información relevante.
 * 
 * - Los roles controlan el acceso a cada funcionalidad (Administrador, Asesor, Académico, Monitor, etc).
 * - Se utiliza Bootstrap para el diseño responsivo y popovers para mostrar descripciones de cada opción.
 * - Al final se muestra el crédito de desarrollo.
 */

$rol = $infoUsuario['rol']; // Obtener el rol del usuario
$extraRol = $infoUsuario['extra_rol']; // Obtener el extra_rol del usuario

// Modal para registrar cursos (solo para roles autorizados)
require_once __DIR__ . '/../components/addUsers/swalAddUser.php';
?>

<div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasWithBothOptionsLabel"><i class="bi bi-boxes"></i> ESCOLINK - Aplicaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="container-fluid sliderbar-scale" style="padding-bottom: 50px;">
            <fieldset class="checkbox-group">
                <legend class="checkbox-group-legend"></legend>
                <!-- Opciones de la barra lateral, cada una controlada por el rol del usuario -->
                <div class="row gx-2 gy-1">
                    <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Añadir usuario">
                                <label class="checkbox-wrapper" onclick="showAddUserSwal()">
                                    <span class="checkbox-tile">
                                        <span class="checkbox-icon">
                                            <i class="bi bi-person-add icono"></i>
                                        </span>
                                        <span class="checkbox-label">Añadir</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Control maestro' || $rol === 'Asesor'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Seguimiento de asistencias">
                                <a href="attendance_tracking.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-journals icono"></i>
                                            </span>
                                            <span class="checkbox-label">Seguimiento</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Toma de asistencias por grupos">
                                <a href="attendance.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-list-task icono"></i>
                                            </span>
                                            <span class="checkbox-label">Asistencias</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Administrar usuarios">
                                <a href="listUsers.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-person-fill-gear icono"></i>
                                            </span>
                                            <span class="checkbox-label">Administrar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Configurar credenciales de correo">
                                <a href="emailConfig.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-envelope-exclamation-fill icono"></i>
                                            </span>
                                            <span class="checkbox-label">Configurar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Editar información de la empresa">
                                <a href="companyConfig.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-building-gear icono"></i>
                                            </span>
                                            <span class="checkbox-label">Editar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Enviar mensaje SMS masivo">
                                <a href="multipleSMS.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon checkbox-icon-lg" style="width: 75px; height: 100px;">
                                                <img src="img/texte_logo.png" alt="CeduLink" style="width: 75px; height: 100px; object-fit: contain;">
                                            </span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Control maestro'): ?>
                        <!-- <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Enviar mensaje SMS masivo">
                                <a href="multipleSMS.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon checkbox-icon-lg" style="width: 75px; height: 100px;">
                                                <img src="img/texte_logo.png" alt="CeduLink" style="width: 75px; height: 100px; object-fit: contain;">
                                            </span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div> -->
                    <?php endif; ?>
                </div>
            </fieldset>
            <!-- Pie de barra lateral con créditos -->
            <div class="text-center mt-2">
                <small class="text-muted" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                    Made by
                    <span style="height: 18px; display: inline-block; vertical-align: middle; margin-bottom: 12px;">
                        <img src="img/eagle_indigo.svg" alt="Eagle Software" style="height: 24px; vertical-align: middle;">
                    </span>
                    <a href="https://www.agenciaeaglesoftware.com/" class="eagle-link">Eagle Software</a>
                </small>
            </div>
        </div>
    </div>
    <?php include("controller/footer.php"); ?>
</div>

<style>
    /* Asegurar espaciado uniforme entre columnas */
    .checkbox-group .row {
        margin-left: -0.5rem;
        margin-right: -0.5rem;
    }

    .checkbox-group .col-4 {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        margin-bottom: 1rem;
    }

    /* Tamaño estándar para todos los botones */
    .checkbox-tile {
        width: 100%;
        height: 85px;
        min-height: 85px;
        max-height: 85px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        transition: all 0.2s ease;
        padding: 8px 4px;
        box-sizing: border-box;
    }

    .checkbox-tile:hover {
        background-color: #e8eafcff;
        border-color: #30336b;
    }

    /* Tamaño estándar para iconos */
    .checkbox-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        margin-bottom: 5px;
    }

    .checkbox-icon .icono {
        font-size: 35px;
        line-height: 1;
    }

    /* Tamaño estándar para etiquetas */
    .checkbox-label {
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        line-height: 1.2;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        word-wrap: break-word;
    }

    /* Asegurar que el wrapper del checkbox ocupe todo el espacio */
    .checkbox-wrapper {
        display: block;
        width: 100%;
        height: 100%;
        text-decoration: none;
    }


    .checkbox-group a {
        text-decoration: none !important;
        color: inherit !important;
    }

    .checkbox-wrapper:hover {
        text-decoration: none;
        color: inherit;
    }

    /* Responsive: En pantallas muy pequeñas usar 2 columnas */
    @media (max-width: 576px) {
        .checkbox-group .col-4 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @font-face {
        font-family: 'Sparose';
        src: url('css/fonts/fonnts.com-Sparose.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
        font-display: swap;
        /* Añade esto para mejor rendimiento */

    }

    .eagle-link {
        font-family: 'Sparose', sans-serif;
        font-size: 1em;
        color: #30336B !important;
        text-decoration: none !important;
    }

    .popover {
        border-color: #30336b !important;

    }

    .popover .popover-arrow {
        --bs-popover-arrow-border: #30336b;
    }
</style>
<script>
    // Inicializar todos los popovers de Bootstrap en la página
    document.addEventListener('DOMContentLoaded', function() {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach(function(popoverTriggerEl) {
            new bootstrap.Popover(popoverTriggerEl, {
                container: 'body'
            });
        });
    });
</script>