<?php

/**
 * ============================================
 * Barra superior y navegación principal (header.php)
 * ============================================
 * Este componente muestra la barra superior fija del dashboard.
 * Incluye el logo, menú principal, accesos rápidos, perfil del usuario y botones flotantes.
 * Las opciones del menú y los accesos dependen del rol del usuario logueado.
 * 
 * - Los roles controlan el acceso a cada funcionalidad (Administrador, Control maestro, Empleabilidad, Permanencia, Académico, etc).
 * - Se integra con los componentes de barra lateral y correo flotante.
 * - Incluye menús desplegables para informes, PQRS, periodos, aulas y perfil.
 * - Permite la descarga de informes con control de tiempo y feedback visual.
 * - El diseño es responsivo y utiliza Bootstrap.
 */

$rol = $infoUsuario['rol']; // Obtener el rol del usuario
$extraRol = $infoUsuario['extra_rol']; // Obtener el extra_rol del usuario

include 'components/importBase/importSwal.php'; 
include 'components/importBase/importSwalSavings.php';
include 'components/sendSMS/smsSaving.php';

// Obtener el logo de la tabla company
$query = "SELECT logo FROM company LIMIT 1";
$result = $conn->query($query);
$company = $result->fetch_assoc();
$logo = $company['logo'] ?? 'gf_header.png'; // Fallback si no hay logo

?>

<nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
    <div class="container-fluid">
        <button class="btn btn-tertiary mr-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptionsLabel">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand" href="#"><img src="img/logos/<?php echo htmlspecialchars($logo); ?>" alt="logo" width="180px"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="main.php">Inicio</a>
                </li>
            </ul>
        </div>

        <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
            <button id="btnSubirBase" class="btn bg-magenta-dark me-2 text-white" type="button">
                <i class="bi bi-cloud-upload me-1"></i>Subir
            </button>
            <button id="btnDescargarPlantilla" class="btn bg-teal-dark text-white me-2" type="button">
                <i class="bi bi-file-earmark-excel"></i>
            </button>
            <!-- NUEVO BOTÓN -->
            <button id="btnInformeGeneral" class="btn bg-indigo-dark text-white" type="button">
                <i class="bi bi-file-earmark-text me-1"></i>Informe General
            </button>
            <script>
                document.getElementById('btnDescargarPlantilla').addEventListener('click', function() {
                    window.location.href = 'uploads/plantilla_base_estudiantes.xlsx';
                });
                
                // NUEVO EVENT LISTENER
                document.getElementById('btnInformeGeneral').addEventListener('click', function() {
                    Swal.fire({
                        title: 'Generando Informe General',
                        text: 'Recopilando información de todos los estudiantes...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch('components/attendance/exportAllStudents.php', {
                        method: 'POST'
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Error en la respuesta del servidor');
                        return response.blob();
                    })
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `Informe_General_Estudiantes_${new Date().toISOString().split('T')[0]}.xlsx`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        
                        Swal.close();
                        Swal.fire({
                            icon: 'success',
                            title: 'Informe generado',
                            text: 'El archivo se ha descargado exitosamente',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo generar el informe'
                        });
                    });
                });
            </script>
        <?php endif; ?>

        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?php echo htmlspecialchars($infoUsuario['foto']); ?>" alt="Perfil" class="rounded-circle" width="40" height="40">
                <?php echo htmlspecialchars($infoUsuario['nombre']); ?>
                <div class="spinner-grow spinner-grow-sm" role="status" style="color:#00976a">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <button type="button" class="btn" data-bs-toggle="popover" data-bs-placement="top" data-bs-content="<?php echo htmlspecialchars($infoUsuario['rol']); ?>" data-bs-trigger="hover">
                    <i class="bi bi-info-circle-fill colorVerde" style="color: #00976a;"></i>
                </button>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <li><a class="dropdown-item" href="profile.php">Perfil</a></li>
                <li><a class="dropdown-item" href="close.php">Cerrar sesión</a></li>
            </ul>
        </div> <!-- Cierre del dropdown -->

    </div> <!-- Cierre del container-fluid -->
</nav>

<!-- Incluir SweetAlert2 si no está ya incluido -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    // Verificar que el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar que el botón existe
        const btnExportar = document.getElementById('btnExportar');
        if (!btnExportar) {
            console.error('El botón btnExportar no fue encontrado en el DOM');
            return;
        }
        
        console.log('Botón exportar encontrado, agregando event listener...');
        
        // Exportar matriz
        btnExportar.addEventListener('click', function(event) {
            event.preventDefault();
            console.log('Click en exportar detectado');
            
            // Verificar que SweetAlert2 esté disponible
            if (typeof Swal === 'undefined') {
                alert('SweetAlert2 no está cargado. No se puede mostrar el diálogo.');
                return;
            }
            
            Swal.fire({
                title: 'Exportando Matriz...',
                text: 'Obteniendo datos y generando archivo Excel.',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            console.log('Iniciando fetch para obtener datos...');

            // Obtener datos via AJAX y generar Excel en el cliente
            fetch('components/individualSearch/obtener_datos_matriz.php')
                .then(response => {
                    console.log('Respuesta recibida:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Datos procesados:', data);
                    if (data.success) {
                        generarExcelCliente(data.data);
                        Swal.fire({
                            title: '¡Exportación completada!',
                            text: 'El archivo Excel se ha descargado exitosamente.',
                            icon: 'success',
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Error al obtener los datos.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error en fetch:', error);
                    Swal.fire('Error', `Error al procesar la solicitud: ${error.message}`, 'error');
                });
        });
    });

    document.getElementById('btnAgregarSede').addEventListener('click', function(event) {
        event.preventDefault();
        Swal.fire({
            title: '¿Qué deseas hacer con las Sedes?',
            text: 'Elige una opción para gestionar las sedes.',
            icon: 'question',
            showConfirmButton: false,
            html: `
                <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 24px;">
                    <button id="swalAgregarSede" class="btn bg-indigo-dark text-white btn-lg" style="width:100%">
                        <i class="bi bi-plus-circle"></i> Agregar Nueva Sede
                    </button>
                    <button id="swalGestionarSede" class="btn bg-teal-dark text-white btn-lg" style="width:100%">
                        <i class="bi bi-list-check"></i> Gestionar Sedes Existentes
                    </button>
                    <button id="swalCancelarSede" class="btn btn-secondary btn-lg" style="width:100%">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                </div>
            `,
            didRender: () => {
                document.getElementById('swalAgregarSede').onclick = function() {
                    Swal.close();
                    // Agregar nueva sede
                    Swal.fire({
                        title: 'Agregar Nueva Sede',
                        input: 'text',
                        inputLabel: 'Nombre de la Sede',
                        inputPlaceholder: 'Ingresa el nombre de la sede',
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        cancelButtonText: 'Cancelar',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'El nombre no puede estar vacío!';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('controller/guardar_sede.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: 'nombre_sede=' + encodeURIComponent(result.value)
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('¡Éxito!', data.message, 'success');
                                    } else {
                                        Swal.fire('Error', data.message, 'error');
                                    }
                                })
                                .catch(error => {
                                    Swal.fire('Error', 'Hubo un problema al guardar.', 'error');
                                });
                        }
                    });
                };
                document.getElementById('swalGestionarSede').onclick = function() {
                    Swal.close();
                    // Gestionar sedes existentes
                    fetch('controller/obtener_sedes.php')
                        .then(response => response.json())
                        .then(sedes => {
                            if (sedes.length === 0) {
                                Swal.fire('Sin Sedes', 'No hay sedes registradas.', 'info');
                                return;
                            }
                            let html = '<div style="max-height: 400px; overflow-y: auto;"><table class="table table-striped table-hover table-bordered"><thead><tr><th>Nombre de Sede</th><th>Fecha de Creación</th><th>Creado por</th><th>Acciones</th></tr></thead><tbody>';
                            sedes.forEach(sede => {
                                html += `<tr><td>${sede.nombre}</td><td>${sede.fecha_creacion}</td><td>${sede.nombre_creador}</td><td><button class="btn btn-danger btn-sm" onclick="eliminarSede(${sede.id})">Eliminar</button></td></tr>`;
                            });
                            html += '</tbody></table></div>';
                            Swal.fire({
                                title: 'Gestionar Sedes Existentes',
                                html: html,
                                showCancelButton: true,
                                cancelButtonText: 'Cerrar',
                                width: '75%'
                            });
                        })
                        .catch(error => {
                            Swal.fire('Error', 'No se pudieron cargar las sedes.', 'error');
                        });
                };
                document.getElementById('swalCancelarSede').onclick = function() {
                    Swal.close();
                };
            }
        });
    });

    document.getElementById('btnAgregarEntrega').addEventListener('click', function(event) {
        event.preventDefault();
        Swal.fire({
            title: '¿Qué deseas hacer con las Categorías?',
            text: 'Elige una opción para gestionar los tipos de entrega.',
            icon: 'question',
            showConfirmButton: false,
            html: `
                <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 24px;">
                    <button id="swalAgregarEntrega" class="btn bg-indigo-dark text-white btn-lg" style="width:100%">
                        <i class="bi bi-plus-circle"></i> Agregar Nuevo Tipo de Entrega
                    </button>
                    <button id="swalGestionarEntrega" class="btn bg-teal-dark text-white btn-lg" style="width:100%">
                        <i class="bi bi-list-check"></i> Gestionar Tipos de Entrega Existentes
                    </button>
                    <button id="swalCancelarEntrega" class="btn btn-secondary btn-lg" style="width:100%">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                </div>
            `,
            didRender: () => {
                document.getElementById('swalAgregarEntrega').onclick = function() {
                    Swal.close();
                    // Agregar nuevo tipo de entrega
                    Swal.fire({
                        title: 'Agregar Nuevo Tipo de Entrega',
                        input: 'text',
                        inputLabel: 'Nombre del Tipo de Entrega',
                        inputPlaceholder: 'Ingresa el nombre del tipo de entrega',
                        showCancelButton: true,
                        confirmButtonText: 'Guardar',
                        cancelButtonText: 'Cancelar',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'El nombre no puede estar vacío!';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('controller/guardar_tipo_entrega.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: 'nombre_tipo_entrega=' + encodeURIComponent(result.value)
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('¡Éxito!', data.message, 'success');
                                    } else {
                                        Swal.fire('Error', data.message, 'error');
                                    }
                                })
                                .catch(error => {
                                    Swal.fire('Error', 'Hubo un problema al guardar.', 'error');
                                });
                        }
                    });
                };
                document.getElementById('swalGestionarEntrega').onclick = function() {
                    Swal.close();
                    // Gestionar tipos de entrega existentes
                    fetch('controller/obtener_tipos_entrega.php')
                        .then(response => response.json())
                        .then(tipos => {
                            if (tipos.length === 0) {
                                Swal.fire('Sin Tipos de Entrega', 'No hay tipos de entrega registrados.', 'info');
                                return;
                            }
                            let html = '<div style="max-height: 400px; overflow-y: auto;"><table class="table table-striped table-hover table-bordered"><thead><tr><th>Nombre del Tipo de Entrega</th><th>Fecha de Creación</th><th>Creado por</th><th>Acciones</th></tr></thead><tbody>';
                            tipos.forEach(tipo => {
                                html += `<tr><td>${tipo.nombre}</td><td>${tipo.fecha_creacion}</td><td>${tipo.nombre_creador}</td><td><button class="btn btn-danger btn-sm" onclick="eliminarTipoEntrega(${tipo.id})">Eliminar</button></td></tr>`;
                            });
                            html += '</tbody></table></div>';
                            Swal.fire({
                                title: 'Gestionar Tipos de Entrega Existentes',
                                html: html,
                                showCancelButton: true,
                                cancelButtonText: 'Cerrar',
                                width: '75%'
                            });
                        })
                        .catch(error => {
                            Swal.fire('Error', 'No se pudieron cargar los tipos de entrega.', 'error');
                        });
                };
                document.getElementById('swalCancelarEntrega').onclick = function() {
                    Swal.close();
                };
            }
        });
    });

    function generarExcelCliente(datos) {
        // Crear un nuevo libro de Excel
        const wb = XLSX.utils.book_new();
        
        // Verificar que hay datos
        if (!datos || datos.length === 0) {
            Swal.fire('Sin datos', 'No hay información para exportar.', 'warning');
            return;
        }

        console.log('Generando Excel con', datos.length, 'registros');
        
        // Configurar el orden de las columnas según la estructura de datos
        const columnOrder = [
            'Número ID',
            'Nombre', 
            'Empresa',
            'Celular',
            'Email',
            'Dirección',
            'Ciudad',
            'Fecha Registro',
            'Género',
            'Fecha Nacimiento',
            'Categoría Regalo',
            'Género Regalo',
            'Última Actualización',
            'Sede Usuario',
            'Tiene Entrega',
            'Fecha Entrega',
            'Año Entrega',
            'Receptor ID',
            'Receptor Nombre',
            'Sede Entrega',
            'Tipo Entrega',
            'Entregado Por',
            'Nombre Entregado Por',
            'URL Firma',
            'URL Foto ID',
            'URL Carta Autorización',
            'Receptor es Usuario',
            'Empresa Receptor',
            'Ciudad Receptor'
        ];

        // Crear array de datos ordenado según las columnas especificadas
        const datosOrdenados = datos.map(row => {
            const newRow = {};
            columnOrder.forEach(col => {
                newRow[col] = row[col] || '';
            });
            return newRow;
        });

        // Convertir datos JSON a hoja de trabajo
        const ws = XLSX.utils.json_to_sheet(datosOrdenados, { header: columnOrder });
        
        // Configurar ancho de columnas específico para cada tipo de dato
        const colWidths = [
            { wch: 12 }, // Número ID
            { wch: 25 }, // Nombre
            { wch: 30 }, // Empresa
            { wch: 15 }, // Celular
            { wch: 30 }, // Email
            { wch: 35 }, // Dirección
            { wch: 20 }, // Ciudad
            { wch: 12 }, // Fecha Registro
            { wch: 10 }, // Género
            { wch: 12 }, // Fecha Nacimiento
            { wch: 18 }, // Categoría Regalo
            { wch: 15 }, // Género Regalo
            { wch: 18 }, // Última Actualización
            { wch: 20 }, // Sede Usuario
            { wch: 12 }, // Tiene Entrega
            { wch: 18 }, // Fecha Entrega
            { wch: 10 }, // Año Entrega
            { wch: 12 }, // Receptor ID
            { wch: 25 }, // Receptor Nombre
            { wch: 20 }, // Sede Entrega
            { wch: 20 }, // Tipo Entrega
            { wch: 15 }, // Entregado Por
            { wch: 25 }, // Nombre Entregado Por
            { wch: 50 }, // URL Firma
            { wch: 50 }, // URL Foto ID
            { wch: 50 }, // URL Carta Autorización
            { wch: 18 }, // Receptor es Usuario
            { wch: 30 }, // Empresa Receptor
            { wch: 20 }  // Ciudad Receptor
        ];
        
        ws['!cols'] = colWidths;
        
        // Obtener el rango de la hoja
        const range = XLSX.utils.decode_range(ws['!ref']);
        
        // Aplicar estilos mejorados a los encabezados
        for (let col = range.s.c; col <= range.e.c; col++) {
            const cellAddress = XLSX.utils.encode_cell({ r: 0, c: col });
            if (!ws[cellAddress]) continue;
            
            ws[cellAddress].s = {
                font: { 
                    bold: true, 
                    color: { rgb: "FFFFFF" },
                    size: 11,
                    name: "Arial"
                },
                fill: { 
                    type: "pattern",
                    pattern: "solid",
                    fgColor: { rgb: "1F4E79" } // Azul profesional
                },
                alignment: { 
                    horizontal: "center", 
                    vertical: "center",
                    wrapText: true
                },
                border: {
                    top: { style: "thin", color: { rgb: "000000" } },
                    bottom: { style: "thin", color: { rgb: "000000" } },
                    left: { style: "thin", color: { rgb: "000000" } },
                    right: { style: "thin", color: { rgb: "000000" } }
                }
            };
        }
        
        // Aplicar estilos a las celdas de datos
        for (let row = range.s.r + 1; row <= range.e.r; row++) {
            for (let col = range.s.c; col <= range.e.c; col++) {
                const cellAddress = XLSX.utils.encode_cell({ r: row, c: col });
                if (!ws[cellAddress]) continue;
                
                // Estilo base para todas las celdas
                ws[cellAddress].s = {
                    font: { 
                        size: 10,
                        name: "Arial"
                    },
                    alignment: { 
                        vertical: "center",
                        wrapText: true
                    },
                    border: {
                        top: { style: "thin", color: { rgb: "D3D3D3" } },
                        bottom: { style: "thin", color: { rgb: "D3D3D3" } },
                        left: { style: "thin", color: { rgb: "D3D3D3" } },
                        right: { style: "thin", color: { rgb: "D3D3D3" } }
                    }
                };
                
                // Alternar colores de fila
                if ((row - 1) % 2 === 0) {
                    ws[cellAddress].s.fill = {
                        type: "pattern",
                        pattern: "solid",
                        fgColor: { rgb: "F8F9FA" }
                    };
                }
                
                // Estilos específicos por columna
                const colName = columnOrder[col];
                if (colName) {
                    switch (colName) {
                        case 'Número ID':
                        case 'Receptor ID':
                            ws[cellAddress].s.alignment.horizontal = "center";
                            break;
                        case 'Tiene Entrega':
                            ws[cellAddress].s.alignment.horizontal = "center";
                            // Colorear según el valor
                            const cellValue = ws[cellAddress].v;
                            if (cellValue === 'SI') {
                                ws[cellAddress].s.fill = {
                                    type: "pattern",
                                    pattern: "solid",
                                    fgColor: { rgb: "D4EDDA" }
                                };
                                ws[cellAddress].s.font.color = { rgb: "155724" };
                            } else if (cellValue === 'NO') {
                                ws[cellAddress].s.fill = {
                                    type: "pattern",
                                    pattern: "solid",
                                    fgColor: { rgb: "F8D7DA" }
                                };
                                ws[cellAddress].s.font.color = { rgb: "721C24" };
                            }
                            break;
                        case 'URL Firma':
                        case 'URL Foto ID':
                        case 'URL Carta Autorización':
                            if (ws[cellAddress].v && ws[cellAddress].v !== 'N/A' && ws[cellAddress].v.trim() !== '') {
                                ws[cellAddress].s.font.color = { rgb: "0066CC" };
                                ws[cellAddress].s.font.underline = true;
                            }
                            break;
                        case 'Fecha Entrega':
                        case 'Fecha Registro':
                        case 'Fecha Nacimiento':
                        case 'Última Actualización':
                            ws[cellAddress].s.alignment.horizontal = "center";
                            break;
                        case 'Año Entrega':
                            ws[cellAddress].s.alignment.horizontal = "center";
                            ws[cellAddress].s.font.bold = true;
                            break;
                    }
                }
            }
        }

        // Configurar altura de filas
        ws['!rows'] = [];
        for (let i = 0; i <= range.e.r; i++) {
            if (i === 0) {
                ws['!rows'][i] = { hpx: 35 }; // Altura mayor para encabezados
            } else {
                ws['!rows'][i] = { hpx: 25 }; // Altura estándar para datos
            }
        }
        
        // Agregar hoja al libro con nombre descriptivo
        XLSX.utils.book_append_sheet(wb, ws, 'Matriz Regalos Metrofem');
        
        // Agregar metadatos al libro
        wb.Props = {
            Title: 'Matriz Completa de Regalos - Metrofem',
            Subject: 'Reporte de entregas de beneficios',
            Author: 'Sistema Gift Flow - Metrofem',
            CreatedDate: new Date()
        };
        
        // Generar nombre del archivo con fecha y hora actual
        const now = new Date();
        const timestamp = now.getFullYear() + '-' + 
                         String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                         String(now.getDate()).padStart(2, '0') + '_' + 
                         String(now.getHours()).padStart(2, '0') + '-' + 
                         String(now.getMinutes()).padStart(2, '0') + '-' + 
                         String(now.getSeconds()).padStart(2, '0');
        
        const filename = `Matriz_Regalos_Metrofem_${timestamp}.xlsx`;

        console.log('Descargando archivo:', filename);
        
        // Descargar el archivo con todas las opciones de formato
        XLSX.writeFile(wb, filename, { 
            bookType: 'xlsx',
            bookSST: false,
            type: 'binary',
            cellStyles: true,
            cellNF: false,
            cellHTML: false,
            cellFormula: true,
            cellDates: false,
            sheetStubs: false
        });
    }

    // Funciones básicas para editar/eliminar (implementa en archivos PHP separados si es necesario)
    function editarSede(id) {
        Swal.fire('Editar', 'Funcionalidad de edición no implementada aún. ID: ' + id, 'info');
    }

    function eliminarSede(id) {
        Swal.fire({
            title: '¿Eliminar sede?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('controller/eliminar_sede.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id_sede=' + id
                    })
                    .then(response => response.json())
                    .then((data) => {
                        if (data.success) {
                            Swal.fire('¡Eliminado!', data.message, 'success').then(() => {
                                // Opcional: Recargar la tabla o cerrar el SWAL
                                location.reload(); // Recarga la página para actualizar la vista
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Hubo un problema al eliminar.', 'error');
                    });
            }
        });
    }

    function eliminarTipoEntrega(id) {
        Swal.fire({
            title: '¿Eliminar tipo de entrega?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('controller/eliminar_tipo_entrega.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id_tipo_entrega=' + id
                    })
                    .then(response => response.json())
                    .then((data) => {
                        if (data.success) {
                            Swal.fire('¡Eliminado!', data.message, 'success').then(() => {
                                // Opcional: Recargar la tabla o cerrar el SWAL
                                location.reload(); // Recarga la página para actualizar la vista
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Hubo un problema al eliminar.', 'error');
                    });
            }
        });
    }
</script>

