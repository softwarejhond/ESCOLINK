<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btnSubirBase').addEventListener('click', function() {
            Swal.fire({
                title: 'Subir Base de Datos',
                html: `
                <div class="container-fluid">
                    <form id="uploadForm" enctype="multipart/form-data" class="mb-3">
                        <div class="mb-3">
                            <label for="excelFile" class="form-label fw-bold">Selecciona el archivo Excel</label>
                            <input type="file" id="excelFile" name="excel_file" accept=".xlsx,.xls" class="form-control" required>
                            <div class="form-text">Archivos soportados: .xlsx o .xls</div>
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
                    return fetch('components/importBase/process_import.php', {
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
                        Swal.fire('Exito', data.message, 'success');
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