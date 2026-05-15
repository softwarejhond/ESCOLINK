<?php
require_once __DIR__ . '/../../controller/conexion.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Obtener los grados disponibles y ordenarlos
$queryGrades = "SELECT DISTINCT grade_level FROM el_students WHERE grade_level IS NOT NULL AND grade_level <> '' ORDER BY grade_level ASC";
$resultGrades = $conn->query($queryGrades);
$grades = [];
if ($resultGrades && $resultGrades->num_rows > 0) {
    while ($row = $resultGrades->fetch_assoc()) {
        $grades[] = $row['grade_level'];
    }
}

usort($grades, function ($a, $b) {
    if (preg_match('/^(\d+)[\s\-]?([A-Za-z]?)$/', $a, $ma)) {
        $numA = (int)$ma[1];
        $letA = strtoupper($ma[2]);
    } else {
        $numA = 999;
        $letA = $a;
    }
    if (preg_match('/^(\d+)[\s\-]?([A-Za-z]?)$/', $b, $mb)) {
        $numB = (int)$mb[1];
        $letB = strtoupper($mb[2]);
    } else {
        $numB = 999;
        $letB = $b;
    }
    return $numA !== $numB ? $numA - $numB : strcmp($letA, $letB);
});
?>

<!-- SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>

<style>
    #studentsTableWrapper {
        display: none;
    }
    .btn-nuevo-estudiante {
        background: linear-gradient(135deg, #1a6fc4, #0d4f9e);
        border: none;
        font-weight: 600;
        letter-spacing: 0.3px;
        box-shadow: 0 3px 10px rgba(26,111,196,0.35);
        transition: transform .15s, box-shadow .15s;
    }
    .btn-nuevo-estudiante:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(26,111,196,0.5);
    }
    #studentsTable th,
    #studentsTable td {
        vertical-align: middle;
        white-space: nowrap;
    }
    .swal-student-form label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 2px;
    }
    .swal2-popup.swal-wide {
        width: 900px !important;
        max-width: 95vw !important;
    }
    .swal2-popup.swal-wide .swal2-html-container {
        overflow-x: hidden;
    }
    .swal-student-form {
        overflow-x: hidden;
    }
    .badge-activo    { background-color: #198754; }
    .badge-retirado  { background-color: #dc3545; }
    .badge-graduado  { background-color: #0d6efd; }
</style>

<div class="container-fluid mt-4">

    <!-- Tarjeta de filtro -->
    <div class="card shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="d-flex align-items-end gap-3" style="flex-wrap: nowrap; overflow-x: auto;">
                <div style="flex: 0 0 260px;">
                    <label class="form-label fw-semibold mb-1">Grupo / Grado</label>
                    <select id="groupGradeLevel" class="form-select">
                        <option value="">Seleccione un grupo</option>
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?= htmlspecialchars($grade) ?>">
                                <?= htmlspecialchars($grade) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button id="btnNuevoEstudiante" class="btn btn-nuevo-estudiante text-white px-4 flex-shrink-0" disabled>
                    <i class="bi bi-person-plus-fill me-1"></i> Nuevo Estudiante
                </button>
                <span id="totalEstudiantes" class="text-muted flex-shrink-0 align-self-center"></span>
            </div>
        </div>
    </div>

    <!-- Placeholder -->
    <div id="studentsPlaceholder" class="text-center py-5 text-muted">
        <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
        Seleccione un grupo para ver el listado de estudiantes.
    </div>

    <!-- Tabla -->
    <div id="studentsTableWrapper">
        <div>
            <table id="studentsTable" class="table table-hover table-bordered w-100">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Tipo Doc.</th>
                        <th>SIMAT</th>
                        <th>Nro. Documento</th>
                        <th>Código</th>
                        <th>Nombre Completo</th>
                        <th>Género</th>
                        <th>Grado</th>
                        <th>Email</th>
                        <th>Celular</th>
                        <th>Celular 2</th>
                        <th>Dirección</th>
                        <th>Barrio</th>
                        <th>Comuna</th>
                        <th>Ciudad</th>
                        <th>Sede</th>
                        <th>Estado</th>
                        <th>F. Registro</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
(function () {
    'use strict';

    // Grados disponibles para el formulario (generados desde PHP)
    const availableGrades = <?= json_encode($grades) ?>;

    let studentsDataTable = null;
    let currentGradeLevel = '';

    // ── Generador de opciones de grado para el formulario ──
    function buildGradeOptions(selectedGrade) {
        let opts = availableGrades.map(function (g) {
            const sel = g === selectedGrade ? ' selected' : '';
            return `<option value="${escHtml(g)}"${sel}>${escHtml(g)}</option>`;
        }).join('');
        // Si el grado seleccionado no está en la lista (grado nuevo), agregar opción
        if (selectedGrade && !availableGrades.includes(selectedGrade)) {
            opts = `<option value="${escHtml(selectedGrade)}" selected>${escHtml(selectedGrade)}</option>` + opts;
        }
        return opts;
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // ── Carga de estudiantes por grupo ──
    function loadStudents(gradeLevel) {
        Swal.fire({
            title: 'Cargando...',
            allowOutsideClick: false,
            didOpen: function () { Swal.showLoading(); }
        });

        $.ajax({
            url: 'components/groups/getStudentsByGroup.php',
            type: 'POST',
            data: { grade_level: gradeLevel },
            dataType: 'json',
            success: function (response) {
                Swal.close();
                renderStudentsTable(response.students || []);
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el listado de estudiantes.' });
            }
        });
    }

    // ── Renderizado de la tabla ──
    function renderStudentsTable(students) {
        if (studentsDataTable) {
            studentsDataTable.destroy();
            studentsDataTable = null;
        }

        const tbody = document.getElementById('studentsTableBody');
        if (!students.length) {
            tbody.innerHTML = '<tr><td colspan="20" class="text-center text-muted py-4">No hay estudiantes registrados en este grupo.</td></tr>';
            document.getElementById('studentsPlaceholder').style.display = 'none';
            document.getElementById('studentsTableWrapper').style.display = 'block';
            document.getElementById('totalEstudiantes').textContent = '';
            return;
        }

        const statusBadge = { ACTIVO: 'badge-activo', RETIRADO: 'badge-retirado', GRADUADO: 'badge-graduado' };

        const rows = students.map(function (s, idx) {
            const badge = statusBadge[s.status] || 'bg-secondary';
            const regDate = s.registration_date ? s.registration_date.split('-').reverse().join('/') : '-';
            const dataJson = escHtml(JSON.stringify(s));

            return `<tr>
                <td class="text-center">${idx + 1}</td>
                <td class="text-center">${escHtml(s.document_type)}</td>
                <td>${escHtml(s.simat)}</td>
                <td>${escHtml(s.document_number)}</td>
                <td>${escHtml(s.student_code)}</td>
                <td>${escHtml(s.name)}</td>
                <td class="text-center">${escHtml(s.gender)}</td>
                <td class="text-center">${escHtml(s.grade_level)}</td>
                <td>${escHtml(s.email)}</td>
                <td>${escHtml(s.cell_phone)}</td>
                <td>${escHtml(s.cell_phone2)}</td>
                <td>${escHtml(s.address)}</td>
                <td>${escHtml(s.barrio)}</td>
                <td>${escHtml(s.comuna)}</td>
                <td>${escHtml(s.city)}</td>
                <td>${escHtml(s.sede)}</td>
                <td class="text-center"><span class="badge ${badge} px-2 py-1">${escHtml(s.status)}</span></td>
                <td class="text-center">${regDate}</td>
                <td class="text-center">
                    <button class="btn btn-sm bg-indigo-dark text-white btn-edit-student" data-student='${dataJson}' title="Editar">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-danger btn-delete-student" data-id="${s.id}" data-name="${escHtml(s.name)}" title="Eliminar">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');

        tbody.innerHTML = rows;

        document.getElementById('studentsPlaceholder').style.display = 'none';
        document.getElementById('studentsTableWrapper').style.display = 'block';
        document.getElementById('totalEstudiantes').textContent = `Total: ${students.length} estudiante${students.length !== 1 ? 's' : ''}`;

        studentsDataTable = $('#studentsTable').DataTable({
            responsive: false,
            scrollX: true,
            autoWidth: false,
            paging: false,
            lengthChange: false,
            language: { url: 'controller/datatable_esp.json' },
            order: [[5, 'asc']],
            columnDefs: [
                { orderable: false, targets: [18, 19] }
            ],
            initComplete: function () {
                this.api().columns.adjust();
            }
        });
        setTimeout(function () {
            if (studentsDataTable) studentsDataTable.columns.adjust();
        }, 150);
    }

    // ── Formulario de estudiante (add / edit) ──
    function buildStudentForm(student) {
        student = student || {};
        const gradeToSelect = student.grade_level || currentGradeLevel;

        return `
        <div class="swal-student-form text-start">
            <div class="row g-2">
                <div class="col-md-4">
                    <label>Tipo Documento <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="sf-doc-type">
                        ${['TI','CC','CE','RC','PAS'].map(t =>
                            `<option value="${t}"${student.document_type === t ? ' selected' : ''}>${t}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Nro. Documento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="sf-doc-number"
                           value="${escHtml(student.document_number)}" placeholder="Solo números">
                </div>
                <div class="col-md-4">
                    <label>Código Estudiante</label>
                    <input type="text" class="form-control form-control-sm" id="sf-student-code"
                           value="${escHtml(student.student_code)}" placeholder="Carné / Matrícula">
                </div>
                <div class="col-md-3">
                    <label>SIMAT</label>
                    <input type="text" class="form-control form-control-sm" id="sf-simat"
                           value="${escHtml(student.simat)}">
                </div>
                <div class="col-md-9">
                    <label>Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="sf-name"
                           value="${escHtml(student.name)}" placeholder="Apellidos y Nombres">
                </div>
                <div class="col-md-3">
                    <label>Grado <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="sf-grade">
                        ${buildGradeOptions(gradeToSelect)}
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Género <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="sf-gender">
                        <option value="M"${student.gender === 'M' ? ' selected' : ''}>Masculino (M)</option>
                        <option value="F"${student.gender === 'F' ? ' selected' : ''}>Femenino (F)</option>
                        <option value="OTRO"${student.gender === 'OTRO' ? ' selected' : ''}>Otro</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Estado <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="sf-status">
                        <option value="ACTIVO"${(student.status||'ACTIVO') === 'ACTIVO' ? ' selected' : ''}>ACTIVO</option>
                        <option value="RETIRADO"${student.status === 'RETIRADO' ? ' selected' : ''}>RETIRADO</option>
                        <option value="GRADUADO"${student.status === 'GRADUADO' ? ' selected' : ''}>GRADUADO</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>F. Registro</label>
                    <input type="date" class="form-control form-control-sm" id="sf-reg-date"
                           value="${escHtml(student.registration_date)}">
                </div>
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" class="form-control form-control-sm" id="sf-email"
                           value="${escHtml(student.email)}">
                </div>
                <div class="col-md-3">
                    <label>Celular</label>
                    <input type="text" class="form-control form-control-sm" id="sf-cell"
                           value="${escHtml(student.cell_phone)}" placeholder="10 dígitos">
                </div>
                <div class="col-md-3">
                    <label>Celular 2</label>
                    <input type="text" class="form-control form-control-sm" id="sf-cell2"
                           value="${escHtml(student.cell_phone2)}" placeholder="10 dígitos">
                </div>
                <div class="col-md-6">
                    <label>Dirección</label>
                    <input type="text" class="form-control form-control-sm" id="sf-address"
                           value="${escHtml(student.address)}">
                </div>
                <div class="col-md-3">
                    <label>Barrio</label>
                    <input type="text" class="form-control form-control-sm" id="sf-barrio"
                           value="${escHtml(student.barrio)}">
                </div>
                <div class="col-md-3">
                    <label>Comuna</label>
                    <input type="text" class="form-control form-control-sm" id="sf-comuna"
                           value="${escHtml(student.comuna)}">
                </div>
                <div class="col-md-4">
                    <label>Ciudad</label>
                    <input type="text" class="form-control form-control-sm" id="sf-city"
                           value="${escHtml(student.city)}">
                </div>
                <div class="col-md-4">
                    <label>Sede</label>
                    <input type="text" class="form-control form-control-sm" id="sf-sede"
                           value="${escHtml(student.sede)}">
                </div>
            </div>
        </div>`;
    }

    // ── Guardar estudiante (AJAX) ──
    function saveStudent(id, formData) {
        return $.ajax({
            url: 'components/groups/saveStudent.php',
            type: 'POST',
            data: formData,
            dataType: 'json'
        });
    }

    // ── Abrir formulario (nuevo o editar) ──
    function openStudentForm(student) {
        const isEdit = !!(student && student.id);
        const title = isEdit ? 'Editar Estudiante' : 'Registrar Nuevo Estudiante';
        const confirmBtnText = isEdit ? '<i class="bi bi-save me-1"></i> Actualizar' : '<i class="bi bi-person-plus me-1"></i> Registrar';

        Swal.fire({
            title: title,
            html: buildStudentForm(student),
            customClass: { popup: 'swal-wide' },
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            confirmButtonText: confirmBtnText,
            confirmButtonColor: '#1a6fc4',
            focusConfirm: false,
            preConfirm: function () {
                const docType    = document.getElementById('sf-doc-type').value;
                const docNumber  = document.getElementById('sf-doc-number').value.trim();
                const studCode   = document.getElementById('sf-student-code').value.trim();
                const simat      = document.getElementById('sf-simat').value.trim();
                const name       = document.getElementById('sf-name').value.trim();
                const grade      = document.getElementById('sf-grade').value;
                const gender     = document.getElementById('sf-gender').value;
                const status     = document.getElementById('sf-status').value;
                const regDate    = document.getElementById('sf-reg-date').value;
                const email      = document.getElementById('sf-email').value.trim();
                const cell       = document.getElementById('sf-cell').value.trim();
                const cell2      = document.getElementById('sf-cell2').value.trim();
                const address    = document.getElementById('sf-address').value.trim();
                const barrio     = document.getElementById('sf-barrio').value.trim();
                const comuna     = document.getElementById('sf-comuna').value.trim();
                const city       = document.getElementById('sf-city').value.trim();
                const sede       = document.getElementById('sf-sede').value.trim();

                if (!docType || !docNumber || !name || !grade || !gender || !status) {
                    Swal.showValidationMessage('Complete los campos obligatorios (*)');
                    return false;
                }
                if (!/^\d+$/.test(docNumber)) {
                    Swal.showValidationMessage('El número de documento debe contener solo dígitos');
                    return false;
                }

                return {
                    id: isEdit ? student.id : '',
                    document_type: docType,
                    document_number: docNumber,
                    student_code: studCode,
                    simat: simat,
                    name: name,
                    grade_level: grade,
                    gender: gender,
                    status: status,
                    registration_date: regDate,
                    email: email,
                    cell_phone: cell,
                    cell_phone2: cell2,
                    address: address,
                    barrio: barrio,
                    comuna: comuna,
                    city: city,
                    sede: sede
                };
            },
            showLoaderOnConfirm: true,
            allowOutsideClick: function () { return !Swal.isLoading(); }
        }).then(function (result) {
            if (!result.isConfirmed) return;

            saveStudent(student ? student.id : null, result.value)
                .done(function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        }).then(function () {
                            if (currentGradeLevel) loadStudents(currentGradeLevel);
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                    }
                })
                .fail(function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo conectar con el servidor.' });
                });
        });
    }

    // ── Eliminar estudiante ──
    function deleteStudent(id, name) {
        Swal.fire({
            title: '¿Archivar estudiante?',
            html: `
                <p class="mb-3">Está a punto de archivar a <strong>${escHtml(name)}</strong>.<br>
                El registro <strong>no se borrará definitivamente</strong>, se moverá al archivo de estudiantes.</p>
                <label class="form-label fw-semibold d-block text-start" style="font-size:0.9rem;">Motivo del retiro <span class="text-muted fw-normal">(opcional)</span></label>
                <textarea id="swal-delete-reason" class="form-control" rows="3"
                    placeholder="Ej: Traslado de institución, retiro voluntario..."></textarea>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Cancelar',
            confirmButtonText: '<i class="bi bi-archive me-1"></i> Sí, archivar',
            preConfirm: function () {
                return document.getElementById('swal-delete-reason').value.trim();
            }
        }).then(function (result) {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Archivando...', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });

            $.ajax({
                url: 'components/groups/deleteStudent.php',
                type: 'POST',
                data: { id: id, delete_reason: result.value },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Archivado',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        }).then(function () {
                            if (currentGradeLevel) loadStudents(currentGradeLevel);
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo archivar el estudiante.' });
                }
            });
        });
    }

    // ── Eventos ──
    $(document).ready(function () {

        // Cambio de grupo
        $('#groupGradeLevel').on('change', function () {
            currentGradeLevel = $(this).val();
            $('#btnNuevoEstudiante').prop('disabled', !currentGradeLevel);

            if (!currentGradeLevel) {
                if (studentsDataTable) { studentsDataTable.destroy(); studentsDataTable = null; }
                $('#studentsTableBody').empty();
                $('#studentsTableWrapper').hide();
                $('#studentsPlaceholder').show();
                $('#totalEstudiantes').text('');
                return;
            }
            loadStudents(currentGradeLevel);
        });

        // Nuevo estudiante
        $('#btnNuevoEstudiante').on('click', function () {
            openStudentForm(null);
        });

        // Editar estudiante (delegación de evento)
        $(document).on('click', '.btn-edit-student', function () {
            let student;
            try {
                student = JSON.parse($(this).attr('data-student'));
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo leer la información del estudiante.' });
                return;
            }
            openStudentForm(student);
        });

        // Eliminar estudiante (delegación de evento)
        $(document).on('click', '.btn-delete-student', function () {
            const id   = $(this).data('id');
            const name = $(this).data('name');
            deleteStudent(id, name);
        });
    });

})();
</script>
