<?php
// Incluir conexión
require_once __DIR__ . '/../../controller/conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
    .table-responsive {
        overflow-x: auto;
        max-width: 100%;
    }

    .datatable {
        width: 100%;
        table-layout: auto;
    }

    .datatable th,
    .datatable td {
        white-space: nowrap;
        min-width: 120px;
        padding: 8px 12px;
        vertical-align: middle;
        text-align: center;
    }

    .datatable th:nth-child(1),
    .datatable td:nth-child(1) {
        min-width: 80px;
    }

    .datatable th:nth-child(2),
    .datatable td:nth-child(2) {
        min-width: 100px;
    }

    .datatable th:nth-child(3),
    .datatable td:nth-child(3) {
        min-width: 200px;
        text-align: left;
    }

    .datatable th:nth-child(4),
    .datatable td:nth-child(4) {
        min-width: 220px;
        text-align: left;
    }

    .datatable th:nth-child(5),
    .datatable td:nth-child(5) {
        min-width: 120px;
    }

    .datatable th:nth-child(6),
    .datatable td:nth-child(6) {
        min-width: 100px;
    }

    /* Columnas de clases dinámicas */
    .datatable th:nth-child(n+7),
    .datatable td:nth-child(n+7) {
        min-width: 80px;
    }

    .observation-modal .modal-dialog {
        max-width: 600px;
    }

    .btn-outline-primary {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }

    .tab-content {
        overflow-x: auto;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }

    /* Colores personalizados */
    .bg-cyan-dark {
        background-color: #007a7a !important;
        color: white !important;
        border-color: #007a7a !important;
    }

    .text-indigo-dark {
        color: #30336b !important;
    }

    .bg-indigo-dark {
        background-color: #30336b !important;
    }

    .nav-tabs .nav-link {
        color: #000000;
        font-weight: normal;
        transition: color 0.3s, font-weight 0.3s;
    }

    .nav-tabs .nav-link.active {
        color: #30336b !important;
        font-weight: bold;
        border-bottom-color: #30336b;
        border-bottom-width: 2px;
    }

    .nav-tabs .nav-link:not(.active) {
        border-color: transparent;
    }

    .nav-tabs .nav-link:hover:not(.active) {
        border-color: transparent;
        color: #30336b;
    }

    .bg-orange-dark {
        background-color: #ff8c00 !important;
        color: white !important;
        border-color: #ff8c00 !important;
    }

    .bg-teal-dark {
        background-color: #008080 !important;
        color: white !important;
        border-color: #008080 !important;
    }

    .observation-btn:hover {
        transform: scale(1.05);
        transition: transform 0.2s ease;
    }

    .observation-btn {
        position: relative;
    }
</style>

<div class="container-fluid mt-4">
    <div class="card shadow mb-3">
        <div class="card-body rounded-0">
            <div class="container-fluid">
                <div class="row align-items-end">
                    <!-- Selección de Grado -->
                    <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                        <label class="form-label">Grado</label>
                        <select id="trackingGradeLevel" class="form-select">
                            <option value="">Seleccione un grado</option>
                            <?php
                            $query = "SELECT DISTINCT grade_level FROM el_students WHERE grade_level IS NOT NULL AND grade_level <> ''";
                            $result = $conn->query($query);

                            $grades = [];
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $grades[] = $row['grade_level'];
                                }
                            }

                            usort($grades, function ($a, $b) {
                                if (preg_match('/^(\d+)[\s\-]?([A-Za-z])$/', $a, $ma)) {
                                    $numA = (int)$ma[1];
                                    $letA = strtoupper($ma[2]);
                                } else {
                                    $numA = 999;
                                    $letA = $a;
                                }
                                if (preg_match('/^(\d+)[\s\-]?([A-Za-z])$/', $b, $mb)) {
                                    $numB = (int)$mb[1];
                                    $letB = strtoupper($mb[2]);
                                } else {
                                    $numB = 999;
                                    $letB = $b;
                                }
                                if ($numA === $numB) {
                                    return strcmp($letA, $letB);
                                }
                                return $numA - $numB;
                            });

                            foreach ($grades as $grade) {
                            ?>
                                <option value="<?= htmlspecialchars($grade) ?>">
                                    <?= htmlspecialchars($grade) ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Botones de exportación -->
                    <!-- <div class="col-lg-6 col-md-6 col-sm-12 col-12 d-flex align-items-end gap-2">
                        <button id="exportTrackingBtn" class="btn btn-success" disabled>
                            <i class="fas fa-file-excel me-2"></i>
                            Exportar seguimiento
                        </button>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor para centrar los Nav tabs -->
<div class="d-flex justify-content-center">
    <ul class="nav nav-tabs" id="studentsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="matematicas-tab" data-bs-toggle="tab" data-bs-target="#matematicas-tab-pane" type="button" role="tab" aria-controls="matematicas-tab-pane" aria-selected="true">
                Matemáticas (<span id="matematicas-count">0</span>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="espanol-tab" data-bs-toggle="tab" data-bs-target="#espanol-tab-pane" type="button" role="tab" aria-controls="espanol-tab-pane" aria-selected="false">
                Español (<span id="espanol-count">0</span>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ingles-tab" data-bs-toggle="tab" data-bs-target="#ingles-tab-pane" type="button" role="tab" aria-controls="ingles-tab-pane" aria-selected="false">
                Inglés (<span id="ingles-count">0</span>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ciencias-tab" data-bs-toggle="tab" data-bs-target="#ciencias-tab-pane" type="button" role="tab" aria-controls="ciencias-tab-pane" aria-selected="false">
                Ciencias (<span id="ciencias-count">0</span>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tecnologia-tab" data-bs-toggle="tab" data-bs-target="#tecnologia-tab-pane" type="button" role="tab" aria-controls="tecnologia-tab-pane" aria-selected="false">
                Tecnología (<span id="tecnologia-count">0</span>)
            </button>
        </li>
    </ul>
</div>

<!-- Contenedor para las tablas de estudiantes -->
<div id="studentsContainer" class="card shadow" style="display: none;">
    <div class="card-body">
        <div class="tab-content mt-3 container-fluid px-0" id="studentsTabContent">

            <?php
            $materias = ['matematicas', 'espanol', 'ingles', 'ciencias', 'tecnologia'];
            foreach ($materias as $index => $materia):
                $isActive = $index === 0 ? 'show active' : '';
            ?>
                <div class="tab-pane fade <?= $isActive ?>" id="<?= $materia ?>-tab-pane" role="tabpanel" aria-labelledby="<?= $materia ?>-tab" tabindex="0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable" id="<?= $materia ?>-table">
                            <thead>
                                <tr>
                                    <th>Tipo ID</th>
                                    <th>Documento</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

<!-- Bootstrap 5.3.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<!-- Incluir SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Inicializar DataTables vacías al cargar la página
        initializeDataTables();

        // Variable global para almacenar los datos cargados
        let currentTrackingData = null;
        let currentGradeLevel = null;

        // Evento de cambio en el selector de grado
        $('#trackingGradeLevel').on('change', function() {
            const selectedGrade = $(this).val();
            
            // Deshabilitar botón de exportación
            $('#exportTrackingBtn').prop('disabled', true);
            currentTrackingData = null;
            currentGradeLevel = null;

            if (!selectedGrade) {
                $('#studentsContainer').hide();
                return;
            }

            currentGradeLevel = selectedGrade;

            // Mostrar carga
            Swal.fire({
                title: 'Cargando estudiantes',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // Cargar datos para todas las materias
            $.ajax({
                url: 'components/attendance/getTrackingData.php',
                method: 'POST',
                data: { grade_level: selectedGrade },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        currentTrackingData = response;

                        // Destruir DataTables existentes
                        $('.datatable').each(function() {
                            if ($.fn.DataTable.isDataTable(this)) {
                                $(this).DataTable().destroy();
                            }
                        });

                        // Poblar las tablas
                        populateTrackingTables(response.data, response.classes);

                        // Mostrar contenedor
                        $('#studentsContainer').show();

                        // Verificar si hay datos
                        let totalStudents = response.total_students || 0;
                        if (totalStudents > 0) {
                            $('#exportTrackingBtn').prop('disabled', false);
                        }

                        // Inicializar DataTables
                        setTimeout(() => {
                            initializeDataTables();
                            setTimeout(() => {
                                Swal.close();
                                if (totalStudents === 0) {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Sin resultados',
                                        text: 'No se encontraron estudiantes para este grado'
                                    });
                                }
                            }, 200);
                        }, 100);

                    } else {
                        Swal.close();
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al cargar estudiantes: ' + response.message
                            });
                        }, 100);
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor'
                        });
                    }, 100);
                    console.error("Error AJAX:", status, error);
                }
            });
        });

        // Exportar seguimiento
        $('#exportTrackingBtn').on('click', function() {
            if (!currentTrackingData || !currentGradeLevel) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No hay datos para exportar'
                });
                return;
            }

            Swal.fire({
                title: 'Generando archivo Excel',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: 'components/attendance/exportTrackingData.php',
                method: 'POST',
                data: {
                    grade_level: currentGradeLevel,
                    data: JSON.stringify(currentTrackingData.data),
                    classes: JSON.stringify(currentTrackingData.classes)
                },
                xhrFields: { responseType: 'blob' },
                success: function(data) {
                    const blob = new Blob([data], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `Seguimiento_${currentGradeLevel}_${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Exportación exitosa',
                        text: 'El archivo se ha descargado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de exportación',
                        text: 'No se pudo generar el archivo Excel'
                    });
                }
            });
        });
    });

    // Función para inicializar DataTables
    function initializeDataTables() {
        const activeTabId = $('.nav-link.active').attr('id');

        $('.datatable').each(function() {
            const tableId = $(this).attr('id');
            const tabId = tableId.replace('-table', '-tab');

            $(`#${tabId}`).tab('show');

            if ($.fn.DataTable.isDataTable(this)) {
                $(this).DataTable().destroy();
            }

            $(this).DataTable({
                searching: true,
                paging: false,
                info: false,
                ordering: true,
                lengthChange: false,
                dom: '<"top"f>rt<"bottom">',
                scrollX: true,
                scrollCollapse: true,
                autoWidth: false,
                columnDefs: [
                    { width: "80px", targets: 0 },
                    { width: "120px", targets: 1 },
                    { width: "200px", targets: 2 },
                    { width: "220px", targets: 3 },
                    { width: "120px", targets: 4 },
                    { width: "100px", targets: 5 },
                    { width: "80px", targets: "_all" }
                ],
                language: {
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros coincidentes",
                    emptyTable: "No hay datos disponibles en la tabla",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros"
                }
            });
        });

        $(`#${activeTabId}`).tab('show');
    }

    // Poblar las tablas de seguimiento
    function populateTrackingTables(data, classes) {
        const courseTypes = ['matematicas', 'espanol', 'ingles', 'ciencias', 'tecnologia'];

        let totalStudents = 0;

        courseTypes.forEach(type => {
            const students = data[type] || [];
            const classData = classes[type] || [];
            totalStudents += students.length;

            const table = $(`#${type}-table`);
            const thead = table.find('thead');
            const tbody = table.find('tbody');
            const countSpan = $(`#${type}-count`);

            // Limpiar tabla completamente
            thead.empty();
            tbody.empty();

            // Crear encabezados base
            const headerRow = $('<tr></tr>');
            headerRow.append('<th>Tipo ID</th>');
            headerRow.append('<th>Documento</th>');
            headerRow.append('<th>Nombre</th>');
            headerRow.append('<th>Correo</th>');
            headerRow.append('<th>Teléfono</th>');
            headerRow.append('<th>Estado</th>');

            // Agregar columnas dinámicas para cada clase
            classData.forEach((classInfo, index) => {
                const dateFormatted = new Date(classInfo.class_date + 'T00:00:00').toLocaleDateString('es-CO', {
                    day: '2-digit', month: '2-digit'
                });
                headerRow.append(`<th title="${classInfo.class_date}">Clase ${index + 1}<br><small>${dateFormatted}</small></th>`);
            });

            // Columna de información de asistencia
            headerRow.append('<th>Seguimiento</th>');

            thead.append(headerRow);

            // Actualizar contador
            countSpan.text(students.length);

            // Llenar tabla
            if (students.length === 0) {
                const colspan = 7 + classData.length;
                tbody.append(`<tr><td colspan="${colspan}" class="text-center">No hay estudiantes registrados</td></tr>`);
            } else {
                students.forEach(student => {
                    const row = $('<tr></tr>');

                    // Celdas base
                    row.append(`<td>${student.document_type || 'N/A'}</td>`);
                    row.append(`<td>${student.document_number || 'N/A'}</td>`);
                    row.append(`<td style="text-align: left;">${student.name || 'N/A'}</td>`);
                    row.append(`<td style="text-align: left;">${student.email || 'N/A'}</td>`);
                    row.append(`<td>${student.cell_phone || 'N/A'}</td>`);
                    row.append(`<td><span class="badge ${getStudentStatusBadge(student.status)}">${student.status || 'N/A'}</span></td>`);

                    // Celdas de asistencia por clase
                    classData.forEach((classInfo, index) => {
                        const classNumber = index + 1;
                        const classDate = classInfo.class_date;

                        const attendanceStatus = classInfo.attendance_by_student && classInfo.attendance_by_student[student.document_number]
                            ? classInfo.attendance_by_student[student.document_number]
                            : null;

                        const buttonClass = getAttendanceButtonClass(attendanceStatus);
                        const attendanceText = getAttendanceStatusText(attendanceStatus);

                        row.append(`<td>
                            <button class="btn btn-sm ${buttonClass} observation-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#genericObservationModal"
                                    data-student-id="${student.document_number}"
                                    data-student-name="${student.name}"
                                    data-grade-level="${student.grade_level}"
                                    data-course-type="${type}"
                                    data-class-date="${classDate}"
                                    data-class-number="${classNumber}"
                                    data-attendance-status="${attendanceStatus || ''}"
                                    data-attendance-text="${attendanceText}"
                                    title="${attendanceText}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>`);
                    });

                    // Celda de seguimiento
                    row.append(`<td>
                        <button class="btn btn-sm bg-teal-dark text-white attendance-info-btn" 
                                data-bs-toggle="modal"
                                data-bs-target="#genericAttendanceModal"
                                data-student-id="${student.document_number}"
                                data-student-name="${student.name}"
                                data-grade-level="${student.grade_level}"
                                data-course-type="${type}">
                            <i class="fas fa-info-circle"></i> Ver
                        </button>
                    </td>`);

                    tbody.append(row);
                });
            }
        });

        // Crear modales genéricos
        createGenericModals();

        // Inicializar Popovers
        initializePopovers();

        // Mostrar primer tab con datos
        const firstTabWithData = courseTypes.find(type => (data[type] || []).length > 0);
        if (firstTabWithData) {
            $(`#${firstTabWithData}-tab`).tab('show');
        }
    }

    function initializePopovers() {
        $('[data-bs-toggle="popover"]').popover('dispose');
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
    }

    function getAttendanceButtonClass(attendanceStatus) {
        switch (attendanceStatus) {
            case 'presente': return 'bg-teal-dark text-white';
            case 'tarde': return 'bg-orange-dark text-white';
            case 'ausente': return 'bg-danger text-white';
            default: return 'bg-secondary text-white';
        }
    }

    function getAttendanceStatusText(attendanceStatus) {
        switch (attendanceStatus) {
            case 'presente': return 'Presente';
            case 'tarde': return 'Llegada tardía';
            case 'ausente': return 'Ausente';
            default: return 'Sin registro';
        }
    }

    function getAttendanceStatusColor(attendanceStatus) {
        switch (attendanceStatus) {
            case 'presente': return 'bg-success';
            case 'tarde': return 'bg-warning text-dark';
            case 'ausente': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    function getStudentStatusBadge(status) {
        switch (status) {
            case 'Activo': return 'bg-success';
            case 'Inactivo': return 'bg-danger';
            case 'Retirado': return 'bg-dark';
            default: return 'bg-secondary';
        }
    }

    // Crear modales genéricos
    function createGenericModals() {
        $('.observation-modal, .attendance-info-modal').remove();

        // Modal genérico para observaciones
        const observationModal = `
        <div class="modal fade observation-modal" id="genericObservationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="observationModalTitle">Observación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3" id="observationCreatedByContainer" style="display: none;">
                            <label class="form-label text-muted">
                                <i class="fas fa-user me-1"></i> Creado por: 
                                <span id="observationCreatedBy" class="fw-bold"></span>
                            </label>
                        </div>
                        <form id="genericObservationForm">
                            <div class="mb-3">
                                <label class="form-label" id="observationStudentLabel"><strong>Estudiante:</strong></label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" id="observationClassLabel"><strong>Fecha de Clase:</strong></label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo de Observación</label>
                                <select class="form-select" name="observation_type" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="no responde">No responde</option>
                                    <option value="condicion de salud">Condición de salud</option>
                                    <option value="dificultades economicas">Dificultades económicas</option>
                                    <option value="dificultades tecnicas con equipo">Dificultades técnicas con equipo</option>
                                    <option value="incompatibilidad con los horarios">Incompatibilidad con los horarios</option>
                                    <option value="insercion academica">Inserción académica</option>
                                    <option value="insercion laboral">Inserción laboral</option>
                                    <option value="inconformidad con el proceso">Inconformidad con el proceso</option>
                                    <option value="inconformidades con el proceso academico">Inconformidades con el proceso académico</option>
                                    <option value="motivos personales">Motivos personales</option>
                                    <option value="otras causas">Otras causas</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observation_text" rows="4" 
                                        placeholder="Escriba sus observaciones aquí..."></textarea>
                            </div>
                            <input type="hidden" name="student_id">
                            <input type="hidden" name="grade_level">
                            <input type="hidden" name="course_type">
                            <input type="hidden" name="class_date">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="saveObservationBtn">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
        `;

        // Modal genérico para información de asistencia / seguimiento
        const attendanceModal = `
        <div class="modal fade attendance-info-modal" id="genericAttendanceModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-indigo-dark text-white">
                        <h5 class="modal-title" id="attendanceModalTitle">
                            <i class="fas fa-user-clock me-2"></i> 
                            Información de Asistencia
                        </h5>
                        <div>
                            <button type="button" class="btn btn-info btn-sm me-2" id="showHistoryBtn">
                                <i class="fas fa-history me-1"></i> Ver historial de gestiones
                            </button>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div id="attendanceLoading" class="text-center p-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando información de asistencia...</p>
                        </div>
                        
                        <div id="attendanceContent" style="display: none;">
                            <!-- Estadísticas de asistencia -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-gradient bg-indigo-dark text-white">
                                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Estadísticas de Asistencia</h6>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="row g-3 w-100">
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-white rounded border-start border-5 border-danger">
                                                <h6 class="text-muted mb-1">Inasistencias/Total</h6>
                                                <div class="fs-2 fw-bold text-danger" id="absencesDisplay">0/0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-white rounded border-start border-5 border-success">
                                                <h6 class="text-muted mb-1">% Asistencia</h6>
                                                <div class="fs-2 fw-bold text-success" id="attendancePercentage">0%</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-white rounded border-start border-5 border-danger">
                                                <h6 class="text-muted mb-1">% Inasistencia</h6>
                                                <div class="fs-2 fw-bold text-danger" id="absencePercentage">0%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Formulario de gestión -->
                            <form id="genericAttendanceForm">
                                <input type="hidden" name="student_id">
                                <input type="hidden" name="grade_level">
                                <input type="hidden" name="course_type">
                                
                                <!-- Gestión de Subsanación -->
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header bg-gradient bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-tasks me-2"></i> Gestión de Subsanación</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-question-circle me-1"></i>
                                                    ¿Requiere subsanación/gestión adicional?
                                                </label>
                                                <select class="form-select form-select-lg" name="requires_intervention">
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="Si">Sí</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-user me-1"></i>
                                                    Responsable
                                                </label>
                                                <input type="text" class="form-control form-control-lg" name="responsible_username" readonly>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-comment-dots me-1"></i>
                                                    Observación Subsanación
                                                </label>
                                                <textarea class="form-control" name="intervention_observation" rows="3" 
                                                         placeholder="Describe las acciones de subsanación realizadas o planificadas..."></textarea>
                                            </div>
                                            <div class="col-lg-6 mx-auto">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    ¿Resuelta?
                                                </label>
                                                <select class="form-select form-select-lg" name="is_resolved">
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="Si">Sí</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Estrategia Adicional -->
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header bg-gradient bg-teal-dark text-white">
                                        <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Estrategia Adicional</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 w-100">
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-cog me-1"></i>
                                                    ¿Requiere estrategia adicional?
                                                </label>
                                                <select class="form-select form-select-lg" name="requires_additional_strategy">
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="Si">Sí</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-chart-line me-1"></i>
                                                    ¿Cumple estrategia?
                                                </label>
                                                <select class="form-select form-select-lg" name="strategy_fulfilled">
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="Si">Sí</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-sticky-note me-1"></i>
                                                    Observación Estrategia
                                                </label>
                                                <textarea class="form-control" name="strategy_observation" rows="3"
                                                         placeholder="Describe la estrategia implementada y su efectividad..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Información de retiro -->
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-gradient bg-secondary text-white">
                                        <h6 class="mb-0"><i class="fas fa-sign-out-alt me-2"></i> Información de Retiro</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 w-100">
                                            <div class="col-lg-8">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Motivo de retiro
                                                </label>
                                                <select class="form-select form-select-lg" name="withdrawal_reason">
                                                    <option value="">No aplica / Estudiante activo</option>
                                                    <option value="Económico">Económico</option>
                                                    <option value="Sociológico">Sociológico</option>
                                                    <option value="Psicológico">Psicológico</option>
                                                    <option value="Institucional">Institucional</option>
                                                    <option value="Académico">Académico</option>
                                                    <option value="Laboral">Laboral</option>
                                                    <option value="Personal">Personal</option>
                                                    <option value="Inconformidad">Inconformidad</option>
                                                    <option value="Inactivo">Inactivo</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-4">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    Fecha de retiro
                                                </label>
                                                <input type="date" class="form-control form-control-lg" name="withdrawal_date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cerrar
                        </button>
                        <button type="button" class="btn bg-indigo-dark text-white btn-lg" id="saveAttendanceBtn">
                            <i class="fas fa-save me-2"></i> Guardar Información
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;

        $('body').append(observationModal);
        $('body').append(attendanceModal);

        setupModalEventListeners();
    }

    // Configurar event listeners para los modales
    function setupModalEventListeners() {
        // Event listener para botones de observación
        $(document).off('click', '.observation-btn').on('click', '.observation-btn', function(e) {
            e.preventDefault();

            const studentId = $(this).data('student-id');
            const studentName = $(this).data('student-name');
            const gradeLevel = $(this).data('grade-level');
            const courseType = $(this).data('course-type');
            const classDate = $(this).data('class-date');
            const classNumber = $(this).data('class-number');
            const attendanceStatus = $(this).data('attendance-status');
            const attendanceText = $(this).data('attendance-text');

            $('#observationModalTitle').text(`Observación - Clase ${classNumber}`);
            $('#observationStudentLabel').html(`<strong>Estudiante:</strong> ${studentName}`);
            $('#observationClassLabel').html(`<strong>Fecha de Clase:</strong> ${classDate}`);

            const attendanceColor = getAttendanceStatusColor(attendanceStatus);
            $('#observationClassLabel').after(`
                <div class="mb-3" id="attendanceStatusLabel">
                    <label class="form-label">
                        <strong>Estado de Asistencia:</strong> 
                        <span class="badge ${attendanceColor}">${attendanceText}</span>
                    </label>
                </div>
            `);

            const form = $('#genericObservationForm');
            form[0].reset();
            form.find('[name="student_id"]').val(studentId);
            form.find('[name="grade_level"]').val(gradeLevel);
            form.find('[name="course_type"]').val(courseType);
            form.find('[name="class_date"]').val(classDate);

            $('#genericObservationModal').data('current-button', this);

            // Cargar observación existente
            loadObservationGeneric(studentId, gradeLevel, courseType, classDate);

            $('#genericObservationModal').modal('show');
        });

        $('#genericObservationModal').on('hidden.bs.modal', function() {
            $('#attendanceStatusLabel').remove();
        });

        // Event listener para botones de seguimiento
        $(document).off('click', '.attendance-info-btn').on('click', '.attendance-info-btn', function(e) {
            e.preventDefault();
            const studentId = $(this).data('student-id');
            const studentName = $(this).data('student-name');
            const gradeLevel = $(this).data('grade-level');
            const courseType = $(this).data('course-type');
            loadAttendanceInfoGeneric(studentId, gradeLevel, courseType, studentName);
        });

        // Guardar observación
        $('#saveObservationBtn').off('click').on('click', function() {
            saveObservationGeneric();
        });

        // Guardar información de asistencia
        $('#saveAttendanceBtn').off('click').on('click', function() {
            saveAttendanceManagementGeneric();
        });

        // Botón de historial
        $(document).off('click', '#showHistoryBtn').on('click', '#showHistoryBtn', function(e) {
            e.preventDefault();

            const modal = $('#genericAttendanceModal');
            const studentId = modal.find('input[name="student_id"]').val();
            const gradeLevel = modal.find('input[name="grade_level"]').val();
            const courseType = modal.find('input[name="course_type"]').val();
            const studentName = modal.find('#attendanceModalTitle').text().replace('Información de Asistencia', '').trim();

            $('#genericAttendanceModal').modal('hide');

            $('#genericAttendanceModal').on('hidden.bs.modal', function() {
                $(this).off('hidden.bs.modal');

                $('#historyModalLabel').html(`<i class="fas fa-history me-2"></i> Historial de Gestiones - ${studentName}`);

                $('#historyModal').data('student-info', {
                    id: studentId,
                    name: studentName,
                    grade_level: gradeLevel,
                    course_type: courseType
                });

                $('#historyModal').modal('show');
                $('#historyLoading').show();
                $('#historyContent').hide();

                loadHistoryData(studentId, gradeLevel, courseType);
            });
        });

        // Exportar historial
        $(document).off('click', '#exportHistoryBtn').on('click', '#exportHistoryBtn', function() {
            const historyData = $('#historyModal').data('history-data');
            const studentInfo = $('#historyModal').data('student-info');

            if (!historyData || !studentInfo) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No hay datos para exportar' });
                return;
            }

            Swal.fire({
                title: 'Generando archivo Excel',
                text: 'Por favor espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: 'components/attendance/exportHistoryToExcel.php',
                method: 'POST',
                data: {
                    student_id: studentInfo.id,
                    student_name: studentInfo.name,
                    grade_level: studentInfo.grade_level,
                    course_type: studentInfo.course_type,
                    history_data: JSON.stringify(historyData)
                },
                xhrFields: { responseType: 'blob' },
                success: function(data) {
                    const blob = new Blob([data], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `Historial_Gestiones_${studentInfo.id}_${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    Swal.close();
                    Swal.fire({ icon: 'success', title: 'Exportación exitosa', text: 'El archivo se ha descargado correctamente', timer: 2000, showConfirmButton: false });
                },
                error: function() {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Error de exportación', text: 'No se pudo generar el archivo Excel' });
                }
            });
        });
    }

    // Cargar observación existente
    function loadObservationGeneric(studentId, gradeLevel, courseType, classDate) {
        $.ajax({
            url: 'components/attendance/getObservation.php',
            method: 'POST',
            data: {
                student_id: studentId,
                grade_level: gradeLevel,
                course_type: courseType,
                class_date: classDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const form = $('#genericObservationForm');
                    form.find('[name="observation_type"]').val(response.data.observation_type);
                    form.find('[name="observation_text"]').val(response.data.observation_text);

                    if (response.data.created_by_name) {
                        $('#observationCreatedBy').text(response.data.created_by_name);
                        $('#observationCreatedByContainer').show();
                    } else {
                        $('#observationCreatedByContainer').hide();
                    }
                } else {
                    $('#observationCreatedByContainer').hide();
                }
            },
            error: function() {
                $('#observationCreatedByContainer').hide();
            }
        });
    }

    // Guardar observación
    function saveObservationGeneric() {
        const form = $('#genericObservationForm');
        const formData = form.serialize();
        const saveButton = $('#saveObservationBtn');
        const currentButton = $('#genericObservationModal').data('current-button');

        const originalText = saveButton.html();
        saveButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'components/attendance/saveObservation.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Éxito', text: 'Observación guardada correctamente', timer: 2000, showConfirmButton: false });
                    if (currentButton) {
                        $(currentButton).removeClass('btn-outline-primary').addClass('bg-cyan-dark');
                    }
                    $('#genericObservationModal').modal('hide');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar la observación: ' + response.message });
                }
            },
            error: function(xhr, status) {
                let errorMessage = 'No se pudo conectar con el servidor';
                if (status === 'timeout') errorMessage = 'La operación tardó demasiado tiempo';
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: errorMessage });
            },
            complete: function() {
                saveButton.prop('disabled', false).html(originalText);
            }
        });
    }

    // Cargar información de asistencia en modal genérico
    function loadAttendanceInfoGeneric(studentId, gradeLevel, courseType, studentName) {
        const modal = $('#genericAttendanceModal');

        modal.find('#attendanceModalTitle').html(`<i class="fas fa-user-clock me-2"></i> Asistencia: ${studentName}`);
        const form = modal.find('#genericAttendanceForm');
        form[0].reset();
        form.find('[name="student_id"]').val(studentId);
        form.find('[name="grade_level"]').val(gradeLevel);
        form.find('[name="course_type"]').val(courseType);

        modal.find('#attendanceLoading').show();
        modal.find('#attendanceContent').hide();

        const username = '<?php echo isset($_SESSION["username"]) ? $_SESSION["username"] : "usuario"; ?>';

        Promise.all([
            $.ajax({
                url: 'components/attendance/getAttendanceStats.php',
                method: 'POST',
                data: { student_id: studentId, grade_level: gradeLevel, course_type: courseType },
                dataType: 'json'
            }),
            $.ajax({
                url: 'components/attendance/getAttendanceManagement.php',
                method: 'POST',
                data: { student_id: studentId, grade_level: gradeLevel, course_type: courseType },
                dataType: 'json'
            })
        ]).then(([statsResponse, managementResponse]) => {
            if (statsResponse && statsResponse.success) {
                const stats = statsResponse.data;
                modal.find('#absencesDisplay').text(stats.absencesDisplay || '0/0');
                modal.find('#attendancePercentage').text(`${stats.attendancePercentage || 0}%`);
                modal.find('#absencePercentage').text(`${stats.absencePercentage || 0}%`);

                const attendanceElement = modal.find('#attendancePercentage');
                attendanceElement.removeClass('text-success text-warning text-danger');
                if (stats.attendancePercentage < 70) attendanceElement.addClass('text-danger');
                else if (stats.attendancePercentage < 85) attendanceElement.addClass('text-warning');
                else attendanceElement.addClass('text-success');
            }

            form.find('[name="responsible_username"]').val(username);
            if (managementResponse && managementResponse.success && managementResponse.data) {
                const data = managementResponse.data;
                form.find('[name="requires_intervention"]').val(data.requires_intervention || '');
                form.find('[name="intervention_observation"]').val(data.intervention_observation || '');
                form.find('[name="is_resolved"]').val(data.is_resolved || '');
                form.find('[name="requires_additional_strategy"]').val(data.requires_additional_strategy || '');
                form.find('[name="strategy_observation"]').val(data.strategy_observation || '');
                form.find('[name="strategy_fulfilled"]').val(data.strategy_fulfilled || '');
                form.find('[name="withdrawal_reason"]').val(data.withdrawal_reason || '');
                form.find('[name="withdrawal_date"]').val(data.withdrawal_date ? data.withdrawal_date.split(' ')[0] : '');
                if (data.responsible_username) form.find('[name="responsible_username"]').val(data.responsible_username);
            }

            modal.find('#attendanceLoading').hide();
            modal.find('#attendanceContent').show();

        }).catch(error => {
            console.error("Error cargando información de asistencia:", error);
            Swal.fire('Error', 'No se pudo cargar la información de asistencia.', 'error');
            modal.find('#attendanceLoading').hide();
        });
    }

    // Guardar gestión de asistencia
    function saveAttendanceManagementGeneric() {
        const form = $('#genericAttendanceForm');
        const formData = form.serialize();
        const saveButton = $('#saveAttendanceBtn');

        const originalText = saveButton.html();
        saveButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'components/attendance/saveAttendanceManagement.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 15000,
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: 'Guardado exitoso', text: 'La información de gestión ha sido guardada correctamente', timer: 2000, showConfirmButton: false });
                    setTimeout(() => { $('#genericAttendanceModal').modal('hide'); }, 2000);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar: ' + (response.message || 'Error desconocido') });
                }
            },
            error: function(xhr, status) {
                let errorMessage = 'No se pudo conectar con el servidor';
                if (status === 'timeout') errorMessage = 'La operación tardó demasiado tiempo';
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: errorMessage });
            },
            complete: function() {
                saveButton.prop('disabled', false).html(originalText);
            }
        });
    }

    // Cargar historial de gestiones
    function loadHistoryData(studentId, gradeLevel, courseType) {
        $.ajax({
            url: 'components/attendance/getAttendanceManagementHistory.php',
            method: 'POST',
            data: {
                student_id: studentId,
                grade_level: gradeLevel,
                course_type: courseType
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#historyModal').data('history-data', response.data);

                    const tbody = $('#historyTable tbody');
                    tbody.empty();

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(item) {
                            const row = $('<tr></tr>');
                            row.append(`<td>${item.formatted_date}</td>`);
                            row.append(`<td>${item.responsible_name || item.responsible_username || 'N/A'}</td>`);
                            row.append(`<td>${item.requires_intervention || 'N/A'}</td>`);

                            const interventionObs = item.intervention_observation
                                ? `<span class="text-truncate d-inline-block" style="max-width: 200px;" title="${item.intervention_observation}">${item.intervention_observation}</span>`
                                : 'N/A';
                            row.append(`<td>${interventionObs}</td>`);
                            row.append(`<td>${item.is_resolved || 'N/A'}</td>`);
                            row.append(`<td>${item.requires_additional_strategy || 'N/A'}</td>`);

                            const strategyObs = item.strategy_observation
                                ? `<span class="text-truncate d-inline-block" style="max-width: 200px;" title="${item.strategy_observation}">${item.strategy_observation}</span>`
                                : 'N/A';
                            row.append(`<td>${strategyObs}</td>`);
                            row.append(`<td>${item.strategy_fulfilled || 'N/A'}</td>`);
                            row.append(`<td>${item.withdrawal_reason || 'N/A'}</td>`);

                            const withdrawalDate = item.withdrawal_date ? new Date(item.withdrawal_date).toLocaleDateString() : 'N/A';
                            row.append(`<td>${withdrawalDate}</td>`);

                            tbody.append(row);
                        });
                    } else {
                        tbody.append(`<tr><td colspan="10" class="text-center">No hay registros de gestión para este estudiante</td></tr>`);
                        $('#exportHistoryBtn').prop('disabled', true);
                    }

                    $('#historyLoading').hide();
                    $('#historyContent').show();
                } else {
                    $('#historyTable tbody').html(`<tr><td colspan="10" class="text-center text-danger">Error al cargar historial</td></tr>`);
                    $('#historyLoading').hide();
                    $('#historyContent').show();
                    $('#exportHistoryBtn').prop('disabled', true);
                }
            },
            error: function() {
                $('#historyTable tbody').html(`<tr><td colspan="10" class="text-center text-danger">Error de conexión al cargar el historial</td></tr>`);
                $('#historyLoading').hide();
                $('#historyContent').show();
                $('#exportHistoryBtn').prop('disabled', true);
            }
        });
    }

    // Modal de historial
    $('body').append(`
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="historyModalLabel">
                        <i class="fas fa-history me-2"></i> Historial de Gestiones
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="historyLoading" class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando historial...</p>
                    </div>
                    <div id="historyContent" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="historyTable">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Responsable</th>
                                        <th>Requiere subsanación</th>
                                        <th>Observación subsanación</th>
                                        <th>Resuelta</th>
                                        <th>Requiere estrategia</th>
                                        <th>Observación estrategia</th>
                                        <th>Estrategia cumplida</th>
                                        <th>Motivo de retiro</th>
                                        <th>Fecha de retiro</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="exportHistoryBtn">
                        <i class="fas fa-file-excel me-2"></i> Exportar Historial
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    `);
</script>