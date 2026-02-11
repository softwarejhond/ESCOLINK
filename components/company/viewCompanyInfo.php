<?php
$query = "SELECT * FROM company LIMIT 1"; // Obtener la primera configuración de empresa
$result = $conn->query($query);
$company = $result->fetch_assoc();
?>

<div class="card mt-4">
    <div class="card-header text-center">
        <h5>Información de la Empresa</h5>
    </div>
    <div class="card-body">
        <form id="companyForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($company['id'] ?? ''); ?>">
            <div class="row justify-content-center">
                <div class="col-4 mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($company['nombre'] ?? 'Sin configurar'); ?>" readonly>
                </div>
                <div class="col-4 mb-3">
                    <label for="nit" class="form-label">NIT</label>
                    <input type="text" class="form-control" id="nit" name="nit" value="<?php echo htmlspecialchars($company['nit'] ?? 'Sin configurar'); ?>" readonly>
                </div>
                <div class="col-4 mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($company['direccion'] ?? 'Sin configurar'); ?>" readonly>
                </div>
                <div class="col-4 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($company['telefono'] ?? 'Sin configurar'); ?>" readonly>
                </div>
                <div class="col-4 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($company['email'] ?? 'Sin configurar'); ?>" readonly>
                </div>
                <div class="col-4 mb-3">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($company['ciudad'] ?? 'Sin configurar'); ?>" readonly>
                </div>
                <div class="col-4 mb-3">
                    <label for="web" class="form-label">Sitio Web</label>
                    <input type="url" class="form-control" id="web" name="web" value="<?php echo htmlspecialchars($company['web'] ?? 'Sin configurar'); ?>" readonly>
                </div>
                <div class="col-4 mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    <?php if (!empty($company['logo'])): ?>
                        <div class="mb-2">
                            <img src="img/logos/<?php echo htmlspecialchars($company['logo']); ?>" alt="Logo actual" style="max-width: 100px; max-height: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*" disabled>
                </div>
            </div>
            <div class="text-center mt-3">
                <button type="button" class="btn bg-indigo-dark text-white" id="editBtn">Editar</button>
                <button type="submit" class="btn bg-lime-dark text-white d-none" id="saveBtn">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('editBtn').addEventListener('click', function() {
    const inputs = document.querySelectorAll('#companyForm input:not([type="file"]), #companyForm textarea');
    inputs.forEach(input => input.removeAttribute('readonly'));
    document.getElementById('logo').removeAttribute('disabled');
    document.getElementById('editBtn').classList.add('d-none');
    document.getElementById('saveBtn').classList.remove('d-none');
});

document.getElementById('companyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('components/company/updateCompany.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Éxito',
                text: 'Cambios guardados exitosamente.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            // Vuelve a readonly y disabled, y oculta el botón guardar
            const inputs = document.querySelectorAll('#companyForm input:not([type="file"]), #companyForm textarea');
            inputs.forEach(input => input.setAttribute('readonly', true));
            document.getElementById('logo').setAttribute('disabled', true);
            document.getElementById('editBtn').classList.remove('d-none');
            document.getElementById('saveBtn').classList.add('d-none');
            // Recargar la página para mostrar cambios
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire({
                title: 'Error',
                text: 'Error al guardar: ' + data.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error de conexión',
            text: 'No se pudo conectar al servidor.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
});
</script>