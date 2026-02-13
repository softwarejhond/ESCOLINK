<?php
// Incluir conexión
require_once __DIR__ . '/../../controller/conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos para la tabla */
        #listaInscritos {
            table-layout: fixed;
        }

        #listaInscritos th,
        #listaInscritos td {
            vertical-align: middle;
            white-space: nowrap;
        }

        #listaInscritos td:nth-child(3) {
            white-space: normal !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .estado-asistencia {
            width: 25px;
            height: 25px;
            margin: auto;
            display: block;
        }
    </style>
</head>

<body>

    <div class="container-fluid mt-4">

        <div class="card shadow mb-3">
            <div class="card-body rounded-0">
                <div class="container-fluid">
                    <div class="row align-items-end">
                        <!-- Selección de Grado -->
                        <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                            <label class="form-label">Grado</label>
                            <select id="grade_level" class="form-select" name="grade_level">
                                <option value="">Seleccione un grado</option>
                                <?php
                                // Obtener los grados y secciones
                                $query = "SELECT DISTINCT grade_level FROM el_students WHERE grade_level IS NOT NULL AND grade_level <> ''";
                                $result = $conn->query($query);

                                $grades = [];
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $grades[] = $row['grade_level'];
                                    }
                                }

                                // Ordenar: primero por número, luego por letra
                                usort($grades, function ($a, $b) {
                                    // Separar número y letra
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

                        <!-- Selección de Materia -->
                        <div class="col-lg-4 col-md-6 col-sm-12 col-12">
                            <label class="form-label">Materia</label>
                            <select id="courseType" class="form-select">
                                <option value="">Seleccione materia</option>
                                <option value="matematicas">Matemáticas</option>
                                <option value="espanol">Español</option>
                                <option value="ingles">Inglés</option>
                                <option value="ciencias">Ciencias</option>
                                <option value="tecnologia">Tecnología</option>
                            </select>
                        </div>

                        <!-- Selección de Fecha -->
                        <div class="col-lg-4 col-md-12 col-sm-12 col-12">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="class_date" id="class_date" class="form-control" required max="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex gap-3">
            <button id="saveAttendance" class="btn bg-magenta-dark text-white">
                <i class="fa fa-save text-white"></i> Guardar Asistencias
            </button>

            <!-- Botón para exportar -->
            <!-- <button type="button" id="exportarExcel" class="btn bg-indigo-dark text-white" disabled>
                <i class="bi bi-file-earmark-excel"></i> Exportar Asistencia
            </button> -->
        </div>

        <!-- Tabla donde se mostrarán los datos -->
        <div class="table-responsive mt-4">
            <table id="listaInscritos" class="table table-hover table-bordered w-100" style="min-width: 1000px;">
                <thead>
                    <tr class="text-center">
                        <th style="width: 10%;">Tipo ID</th>
                        <th style="width: 12%;">Número de ID</th>
                        <th style="width: 28%; min-width: 250px;">Nombre completo</th>
                        <th style="width: 28%; min-width: 250px;">Correo</th>
                        <th style="width: 7%;">Presente</th>
                        <th style="width: 7%;">Tarde</th>
                        <th style="width: 7%;">Ausente</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- jQuery para la solicitud AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            // Función para validar los campos y habilitar los botones de exportar
            const validateExportButton = () => {
                const gradeLevel = $('#grade_level').val();
                const courseType = $('#courseType').val();
                const fecha = $('#class_date').val();

                // Habilitar los botones si todos los campos están completos
                if (gradeLevel && courseType && fecha) {
                    $('#exportarExcel').prop('disabled', false);
                } else {
                    $('#exportarExcel').prop('disabled', true);
                }
            };

            // Función para actualizar la tabla
            const updateTable = () => {
                const data = {
                    grade_level: $('#grade_level').val(),
                    courseType: $('#courseType').val(),
                    class_date: $('#class_date').val()
                };

                // Verificar que todos los campos requeridos tengan valor
                if (!data.grade_level || !data.courseType || !data.class_date) {
                    console.log('Por favor, complete todos los campos');
                    $('#listaInscritos tbody').html('');
                    return;
                }

                $.ajax({
                    url: 'components/attendance/buscar_datos.php',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: (response) => {
                        // Verificar si ya existe un registro de asistencia
                        if (response.exists) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Asistencia ya registrada',
                                text: response.message,
                                confirmButtonText: 'Entendido'
                            });
                            $('#saveAttendance').prop('disabled', true);
                            $('#listaInscritos tbody').html('<tr><td colspan="7" class="text-center">Ya existe asistencia registrada para este grado y materia en esta fecha</td></tr>');
                            return;
                        }

                        // Resetear el estado del botón de guardar
                        $('#saveAttendance').prop('disabled', false);

                        if (response && response.html) {
                            $('#listaInscritos tbody').html(response.html);

                            // Habilitar el botón de exportar cuando hay datos
                            validateExportButton();
                        } else {
                            $('#listaInscritos tbody').html('<tr><td colspan="7" class="text-center">No se encontraron registros</td></tr>');
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error('Error en la solicitud:', error);
                        $('#listaInscritos tbody').html('<tr><td colspan="7" class="text-center">Error al cargar los datos</td></tr>');
                    }
                });
            };

            // Actualizar la tabla cuando se cambie algún filtro
            $('#grade_level, #courseType, #class_date').change(function() {
                validateExportButton();
                updateTable();
            });

            // Verificar estado inicial del botón de exportar
            validateExportButton();
        });

        // Guardar asistencia
        $('#saveAttendance').click(function() {
            const attendanceData = {};

            $('input[type="radio"]:checked').each(function() {
                const studentId = $(this).attr('name').split('_')[2];
                const status = $(this).data('estado');
                attendanceData[studentId] = status;
            });

            if (Object.keys(attendanceData).length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin datos',
                    text: 'Debe marcar al menos una asistencia para guardar'
                });
                return;
            }

            const postData = {
                grade_level: $('#grade_level').val(),
                courseType: $('#courseType').val(),
                class_date: $('#class_date').val(),
                attendance: attendanceData
            };

            $.ajax({
                url: 'components/attendance/guardar_asistencia.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(postData),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Asistencias guardadas correctamente'
                        }).then((result) => {
                            // Recargar la página después de cerrar el alert
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Error desconocido'
                        });
                    }
                },
                error: function(xhr) {
                    alert('Error en la solicitud: ' + xhr.responseText);
                }
            });
        });

        $('#exportarExcel').click(function() {
            // Verificar que se haya seleccionado todo
            const gradeLevel = $('#grade_level').val();
            const courseType = $('#courseType').val();
            const fecha = $('#class_date').val();

            if (!gradeLevel || !courseType || !fecha) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Información incompleta',
                    text: 'Por favor, seleccione grado, materia y fecha para exportar'
                });
                return;
            }

            // Crear formulario dinámico para exportar
            const form = $('<form>', {
                method: 'POST',
                action: 'components/attendance/exportar_listado.php'
            });

            form.append($('<input>', {
                type: 'hidden',
                name: 'grade_level',
                value: gradeLevel
            }));

            form.append($('<input>', {
                type: 'hidden',
                name: 'course_type',
                value: courseType
            }));

            form.append($('<input>', {
                type: 'hidden',
                name: 'class_date',
                value: fecha
            }));

            $('body').append(form);
            form.submit();
            form.remove();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Alerta informativa al cargar -->
    <script>
        $(document).ready(function() {
            Swal.fire({
                icon: 'info',
                title: 'Recordatorio',
                text: 'Solo puede registrar asistencia una vez por grado y materia en cada fecha. Por favor, asegúrese de completar toda la información correctamente.',
                confirmButtonText: 'Entendido'
            });
        });
    </script>

</body>

</html>