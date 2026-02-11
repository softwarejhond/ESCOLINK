<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btnAhorros').addEventListener('click', function() {
            Swal.fire({
                title: 'Subir Base de Datos para SMS de Ahorros',
                html: `
                <div class="container-fluid">
                    <form id="uploadForm" enctype="multipart/form-data" class="mb-3">
                        <div class="mb-3">
                            <label for="excelFile" class="form-label fw-bold">Selecciona el archivo Excel</label>
                            <input type="file" id="excelFile" name="excel_file" accept=".xlsx,.xls" class="form-control" required>
                            <div class="form-text">Archivos soportados: .xlsx o .xls</div>
                            <small class="text-muted" style="font-size: 0.85em;">
                                <i class="bi bi-info-circle" style="font-size: 1em; vertical-align: middle;"></i>
                                La columna de números de teléfono debe contener solo números válidos.
                            </small>
                        </div>
                    </form>
                </div>
            `,
                showCancelButton: true,
                confirmButtonText: 'Subir',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true, // Mostrar loader durante la confirmación
                preConfirm: () => {
                    const file = document.getElementById('excelFile').files[0];
                    if (!file) {
                        Swal.showValidationMessage('Debes seleccionar un archivo');
                        return false;
                    }
                    const formData = new FormData();
                    formData.append('excel_file', file);

                    // Devolver la promesa del fetch para que el loader se maneje correctamente
                    return fetch('components/importBase/process_import_savings.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json());
                }
            }).then((result) => {
                // Manejar el resultado aquí para marcar como satisfactorio
                if (result.isConfirmed && result.value) {
                    const data = result.value;
                    if (data.success) {
                        Swal.fire('Éxito', data.message, 'success');
                    } else {
                        Swal.fire('Error', data.message + (data.errors ? '<br>Errores: ' + data.errors.join('<br>') : ''), 'error');
                    }
                }
            }).catch((error) => {
                Swal.fire('Error', 'Hubo un problema al subir el archivo.', 'error');
            });
        });
    });
</script>