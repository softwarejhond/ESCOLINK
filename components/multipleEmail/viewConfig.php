<?php
$query = "SELECT id, username, host, email, password, port, dependence, Subject FROM smtpconfig LIMIT 1"; // Obtener la primera configuración
$result = $conn->query($query);
$config = $result->fetch_assoc();
?>

<div class="card mt-4">
    <div class="card-header text-center">
        <h5>Configuración SMTP</h5>
    </div>
    <div class="card-body">
        <form id="smtpForm">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($config['id'] ?? ''); ?>">
            <div class="row justify-content-center">
                <div class="col-4 mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($config['username'] ?? 'Sin configurar'); ?>" readonly>
                </div>
                <div class="col-4 mb-3">
                    <label for="host" class="form-label">Host</label>
                    <input type="text" class="form-control" id="host" name="host" value="<?php echo htmlspecialchars($config['host'] ?? 'Sin configurar'); ?>" readonly>
                </div>
                <div class="col-4 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($config['email'] ?? 'Sin configurar'); ?>" readonly>
                </div>
                <div class="col-4 mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($config['password'] ?? ''); ?>" readonly>
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="col-4 mb-3">
                    <label for="port" class="form-label">Puerto</label>
                    <input type="number" class="form-control" id="port" name="port" value="<?php echo htmlspecialchars($config['port'] ?? 'Sin configurar'); ?>" readonly>
                </div>
                <div class="col-4 mb-3">
                    <label for="dependence" class="form-label">Dependencia</label>
                    <textarea class="form-control" id="dependence" name="dependence" readonly><?php echo htmlspecialchars($config['dependence'] ?? 'Sin configurar'); ?></textarea>
                </div>
                <div class="col-4 mb-3">
                    <label for="subject" class="form-label">Asunto</label>
                    <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($config['Subject'] ?? 'Sin configurar'); ?>" readonly>
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
    const inputs = document.querySelectorAll('#smtpForm input, #smtpForm textarea');
    inputs.forEach(input => input.removeAttribute('readonly'));
    document.getElementById('editBtn').classList.add('d-none');
    document.getElementById('saveBtn').classList.remove('d-none');
});

document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

document.getElementById('smtpForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('components/multipleEmail/updateSmtp.php', {
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
            // Vuelve a readonly y oculta el botón guardar
            const inputs = document.querySelectorAll('#smtpForm input, #smtpForm textarea');
            inputs.forEach(input => input.setAttribute('readonly', true));
            document.getElementById('editBtn').classList.remove('d-none');
            document.getElementById('saveBtn').classList.add('d-none');
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