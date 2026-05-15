<?php
require_once __DIR__ . '/../../controller/conexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Obtener grupos que tienen registros en attendance_records
$queryGrades = "SELECT DISTINCT grade_level FROM attendance_records
                WHERE grade_level IS NOT NULL AND grade_level <> ''
                ORDER BY grade_level ASC";
$resultGrades = $conn->query($queryGrades);
$grades = [];
if ($resultGrades) {
    while ($row = $resultGrades->fetch_assoc()) {
        $grades[] = $row['grade_level'];
    }
}

usort($grades, function ($a, $b) {
    if (preg_match('/^(\d+)[\s\-]?([A-Za-z]?)$/', $a, $ma)) {
        $numA = (int)$ma[1]; $letA = strtoupper($ma[2]);
    } else { $numA = 999; $letA = $a; }
    if (preg_match('/^(\d+)[\s\-]?([A-Za-z]?)$/', $b, $mb)) {
        $numB = (int)$mb[1]; $letB = strtoupper($mb[2]);
    } else { $numB = 999; $letB = $b; }
    return $numA !== $numB ? $numA - $numB : strcmp($letA, $letB);
});
?>

<!-- SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>

<style>
    #editAttWrapper { display: none; }

    .att-radio {
        width: 24px !important;
        height: 24px !important;
        cursor: pointer;
        flex-shrink: 0;
    }
    .att-radio[value="presente"] { accent-color: #198754; }
    .att-radio[value="tarde"]    { accent-color: #fd7e14; }
    .att-radio[value="ausente"]  { accent-color: #dc3545; }

    #editAttTable th,
    #editAttTable td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .badge-presente { background-color: #198754; }
    .badge-tarde    { background-color: #fd7e14; }
    .badge-ausente  { background-color: #dc3545; }

    .history-swal-table th { background-color: #343a40; color: #fff; font-size: 0.82rem; }
    .history-swal-table td { font-size: 0.82rem; }

    .swal2-popup.swal-history-wide {
        width: 900px !important;
        max-width: 95vw !important;
    }
    .swal2-popup.swal-history-wide .swal2-html-container {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>

<div class="container-fluid mt-4">

    <!-- Tarjeta de filtros -->
    <div class="card shadow-sm mb-3">
        <div class="card-body py-3 w-100">
            <div class="d-flex align-items-end w-100 gap-3" style="flex-wrap: nowrap;">
                <div style="flex: 3 1 220px; min-width: 0;">
                    <label class="form-label fw-semibold mb-1">Grupo / Grado</label>
                    <select id="editGradeLevel" class="form-select">
                        <option value="">Seleccione un grupo</option>
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?= htmlspecialchars($grade) ?>">
                                <?= htmlspecialchars($grade) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 2 1 160px; min-width: 0;">
                    <label class="form-label fw-semibold mb-1">Fecha de clase</label>
                    <input type="date" id="editClassDate" class="form-control"
                           max="<?= date('Y-m-d') ?>">
                </div>
                <button id="btnSaveAttEdit" class="btn text-white" style="flex: 2 1 160px; min-width: 0; background:linear-gradient(135deg,#198754,#0f6944);border:none;font-weight:600;box-shadow:0 3px 8px rgba(25,135,84,.35);"
                        disabled>
                    <i class="bi bi-floppy me-1"></i> Guardar Cambios
                </button>
                <button id="btnViewHistory" class="btn btn-outline-secondary" style="flex: 1 1 130px; min-width: 0;" disabled>
                    <i class="bi bi-clock-history me-1"></i> Ver Historial
                </button>
            </div>
        </div>
    </div>

    <!-- Placeholder -->
    <div id="editAttPlaceholder" class="text-center py-5 text-muted">
        <i class="bi bi-calendar3 fs-1 d-block mb-2 opacity-50"></i>
        Seleccione un grupo y una fecha para cargar la asistencia registrada.
    </div>

    <!-- Tabla de edición -->
    <div id="editAttWrapper">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span id="editAttInfo" class="text-muted small"></span>
        </div>
        <div class="table-responsive">
            <table id="editAttTable" class="table table-hover table-bordered w-100">
                <thead class="table-dark text-center">
                    <tr>
                        <th style="width:8%;">Tipo ID</th>
                        <th style="width:13%;">Número ID</th>
                        <th style="width:30%;">Nombre Completo</th>
                        <th style="width:25%;">Correo</th>
                        <th style="width:8%;">Presente</th>
                        <th style="width:8%;">Tarde</th>
                        <th style="width:8%;">Ausente</th>
                    </tr>
                </thead>
                <tbody id="editAttBody">
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
(function () {
    'use strict';

    let currentGrade = '';
    let currentDate  = '';

    // ── Cargar asistencia para edición ──
    function loadAttendance() {
        currentGrade = $('#editGradeLevel').val();
        currentDate  = $('#editClassDate').val();

        if (!currentGrade || !currentDate) {
            resetView();
            return;
        }

        Swal.fire({
            title: 'Cargando asistencia...',
            allowOutsideClick: false,
            didOpen: function () { Swal.showLoading(); }
        });

        $.ajax({
            url: 'components/attendance/getAttendanceForEdit.php',
            type: 'POST',
            data: { grade_level: currentGrade, class_date: currentDate },
            dataType: 'json',
            success: function (res) {
                Swal.close();
                if (!res.exists) {
                    resetView();
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin registros',
                        text: res.message,
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }
                $('#editAttBody').html(res.html);
                $('#editAttInfo').text('Total de estudiantes en el registro: ' + res.count);
                $('#editAttPlaceholder').hide();
                $('#editAttWrapper').show();
                $('#btnSaveAttEdit').prop('disabled', false);
                $('#btnViewHistory').prop('disabled', false);
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la asistencia.' });
            }
        });
    }

    // ── Guardar cambios ──
    function saveChanges() {
        const attendance = [];
        $('#editAttBody tr').each(function () {
            const checked = $(this).find('.att-radio:checked');
            if (checked.length) {
                attendance.push({
                    student_id: checked.data('student-id'),
                    status:     checked.val()
                });
            }
        });

        if (!attendance.length) {
            Swal.fire({ icon: 'warning', title: 'Sin datos', text: 'No hay registros de asistencia para guardar.' });
            return;
        }

        Swal.fire({
            title: '¿Guardar cambios?',
            text: 'Se actualizarán los registros de asistencia modificados.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonText: 'Cancelar',
            confirmButtonText: '<i class="bi bi-floppy me-1"></i> Sí, guardar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Guardando...',
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });

            $.ajax({
                url: 'components/attendance/saveAttendanceEdit.php',
                type: 'POST',
                data: {
                    grade_level: currentGrade,
                    class_date:  currentDate,
                    attendance:  JSON.stringify(attendance)
                },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        const icon = res.changes > 0 ? 'success' : 'info';
                        Swal.fire({ icon: icon, title: res.changes > 0 ? 'Guardado' : 'Sin cambios', text: res.message, timer: 2000, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar.' });
                }
            });
        });
    }

    // ── Ver historial ──
    function viewHistory() {
        Swal.fire({
            title: 'Cargando historial...',
            allowOutsideClick: false,
            didOpen: function () { Swal.showLoading(); }
        });

        $.ajax({
            url: 'components/attendance/getAttendanceEditHistory.php',
            type: 'POST',
            data: { grade_level: currentGrade, class_date: currentDate },
            dataType: 'json',
            success: function (res) {
                if (!res.records || !res.records.length) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin historial',
                        text: 'No hay ediciones registradas para este grupo y fecha.',
                    });
                    return;
                }

                const dateFormatted = currentDate.split('-').reverse().join('/');
                const escapedGrade  = $('<span>').text(currentGrade).html();

                const rows = res.records.map(function (r) {
                    const editedAt = r.edited_at ? r.edited_at.substring(0, 16).replace('T', ' ') : '-';
                    const oldBadge = `<span class="badge badge-${r.old_status} px-2">${r.old_status}</span>`;
                    const newBadge = `<span class="badge badge-${r.new_status} px-2">${r.new_status}</span>`;
                    return `<tr>
                        <td>${escHtml(r.edited_at ? r.edited_at.substring(0,16).replace('T',' ') : '-')}</td>
                        <td>${escHtml(r.editor_username)}</td>
                        <td>${escHtml(r.student_name || r.student_id)}</td>
                        <td class="text-center">${oldBadge}</td>
                        <td class="text-center">${newBadge}</td>
                    </tr>`;
                }).join('');

                const tableHtml = `
                    <p class="text-muted small text-start mb-2">
                        Grupo: <strong>${escapedGrade}</strong> &nbsp;|&nbsp; Fecha de clase: <strong>${dateFormatted}</strong>
                    </p>
                    <table class="table table-bordered table-sm history-swal-table">
                        <thead>
                            <tr>
                                <th>Fecha edición</th>
                                <th>Editor</th>
                                <th>Estudiante</th>
                                <th class="text-center">Estado anterior</th>
                                <th class="text-center">Estado nuevo</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>`;

                Swal.fire({
                    title: 'Historial de ediciones',
                    html: tableHtml,
                    customClass: { popup: 'swal-history-wide' },
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#6c757d'
                });
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el historial.' });
            }
        });
    }

    function resetView() {
        $('#editAttBody').empty();
        $('#editAttWrapper').hide();
        $('#editAttPlaceholder').show();
        $('#btnSaveAttEdit').prop('disabled', true);
        $('#btnViewHistory').prop('disabled', true);
        $('#editAttInfo').text('');
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    // ── Eventos ──
    $(document).ready(function () {

        $('#editGradeLevel, #editClassDate').on('change', function () {
            loadAttendance();
        });

        $('#btnSaveAttEdit').on('click', function () {
            saveChanges();
        });

        $('#btnViewHistory').on('click', function () {
            viewHistory();
        });
    });

})();
</script>
