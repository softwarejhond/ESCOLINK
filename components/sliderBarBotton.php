<?php
/**
 * ============================================
 * Barra inferior de gestión de matriculados (sliderBarBotton.php)
 * ============================================
 * Este componente muestra una barra inferior con accesos rápidos a funciones de gestión de matriculados y asistencia.
 * Las opciones visibles dependen del rol y extra_rol del usuario logueado.
 * Cada botón puede abrir una página específica, como asistencia individual o grupal, matrícula múltiple, edición de cursos, usuarios, horarios, puntajes, etc.
 * 
 * - Los roles controlan el acceso a cada funcionalidad (Administrador, Académico, Docente, Control maestro, Mentor, Monitor, Asesor, Supervisor, Permanencia).
 * - Se utiliza Bootstrap para el diseño responsivo y popovers para mostrar descripciones de cada opción.
 * - El diseño es horizontal y responsivo, pensado para facilitar la gestión rápida desde la parte inferior del dashboard.
 */

$rol = $infoUsuario['rol']; // Obtener el rol del usuario
$extraRol = $infoUsuario['extra_rol'] ?? ''; // Obtener el extra_rol del usuario
?>
<style>
    /* Espaciado uniforme entre botones */
    .checkbox-group-bottom {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: flex-start;
        margin-left: 0;
        margin-right: 0;
        gap: 0.5rem;
    }

    .checkbox-group-bottom .checkbox {
        display: flex;
        justify-content: center;
        align-items: center;
        padding-left: 0;
        padding-right: 0;
        margin-bottom: 1rem;
    }

    /* Tamaño estándar para todos los botones */
    .checkbox-tile {
        width: 85px;
        height: 85px;
        min-width: 85px;
        min-height: 85px;
        max-width: 85px;
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

    .checkbox-group-bottom a {
        text-decoration: none !important;
        color: inherit !important;
    }

    .checkbox-wrapper:hover {
        text-decoration: none;
        color: inherit;
    }

    /* Responsive: En pantallas muy pequeñas usar 2 columnas */
    @media (max-width: 576px) {
        .checkbox-group-bottom .checkbox {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }
</style>
<div class="offcanvas offcanvas-bottom text-bg-dark" tabindex="-1" id="offcanvasBottom" aria-labelledby="offcanvasBottomLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasBottomLabel"><i class="bi bi-boxes"></i>   SYGNIA - Gestión de matriculados</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body small">
        <fieldset class="checkbox-group-bottom d-flex flex-wrap justify-content-center align-items-center">

            <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                <div class="checkbox me-3 text-center"
                    data-bs-toggle="popover"
                    data-bs-trigger="hover focus"
                    data-bs-placement="bottom"
                    data-bs-content="Administrar usuarios">
                    <a href="editUsers.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-person-fill-gear icono text-indigo-dark"></i>
                                </span>
                                <span class="checkbox-label">Usuarios</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>
        </fieldset>

    </div>
</div>
<script>
    // Inicializar todos los popovers de Bootstrap en la página
    document.addEventListener('DOMContentLoaded', function () {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach(function (popoverTriggerEl) {
            new bootstrap.Popover(popoverTriggerEl, { container: 'body' });
        });
    });
</script>