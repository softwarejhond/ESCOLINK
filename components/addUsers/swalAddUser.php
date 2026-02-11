<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showAddUserSwal() {
    // Cerrar el offcanvas antes de mostrar el SweetAlert para evitar bloqueo de interacciones
    const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasWithBothOptions'));
    if (offcanvas) offcanvas.hide();

    const userRol = '<?php echo $rol; ?>'; // Rol del usuario logueado (string, asumiendo conversión si es necesario)
    let rolOptions = '<option value="1">Administrador</option><option value="3">Asesor</option>';
    if (userRol === 'Control maestro') {
        rolOptions += '<option value="12">Control maestro</option>';
    }

    Swal.fire({
        title: 'Añadir Usuario',
        html: `
            <form id="addUserForm" enctype="multipart/form-data">
                <div class="container-fluid"> 
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="username" class="form-label">Cédula</label>
                            <input type="number" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <span class="input-group-text" onclick="togglePassword('password')"><i class="bi bi-eye" id="icon-password"></i></span>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="confirmPassword" class="form-label">Confirmar Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                <span class="input-group-text" onclick="togglePassword('confirmPassword')"><i class="bi bi-eye" id="icon-confirmPassword"></i></span>
                            </div>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required onchange="toggleSedes()">
                                ${rolOptions}
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="genero" class="form-label">Género</label>
                            <select class="form-select" id="genero" name="genero" required>
                                <option value="Hombre">Hombre</option>
                                <option value="Mujer">Mujer</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="foto" class="form-label">Foto</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept=".png,.jpg,.jpeg">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-6 mb-3">
                                <label for="edad" class="form-label">Edad</label>
                                <input type="number" class="form-control" id="edad" name="edad" required>
                            </div>
                        </div>
                    </div>
                    <div id="sedesSection" style="display: none;">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="sede1" class="form-label">Sede 1</label>
                                <select class="form-select" id="sede1" name="sede1"></select>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="sede2" class="form-label">Sede 2</label>
                                <select class="form-select" id="sede2" name="sede2"></select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Añadir',
        width: '75%', // Cambiar a porcentaje para responsividad y evitar scroll en pantallas pequeñas
        preConfirm: () => {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            if (password !== confirmPassword) {
                Swal.showValidationMessage('Las contraseñas no coinciden.');
                return false;
            }
            if (!/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/.test(password)) {
                Swal.showValidationMessage('La contraseña debe ser alfanumérica con al menos un carácter especial y mínimo 8 caracteres.');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData(document.getElementById('addUserForm'));
            fetch('components/addUsers/addUser.php', { // Ruta completa relativa al archivo addUser.php
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', 'Usuario añadido correctamente.', 'success');
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => Swal.fire('Error', 'Error en la solicitud.', 'error'));
        }
    });
}

function togglePassword(id) {
    const input = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function toggleSedes() {
    const rol = document.getElementById('rol').value;
    const sedesSection = document.getElementById('sedesSection');
    if (rol == 3) {
        sedesSection.style.display = 'block';
        loadSedes();
    } else {
        sedesSection.style.display = 'none';
    }
}

function loadSedes() {
    fetch('controller/obtener_sedes.php')
    .then(response => response.json())
    .then(sedes => {
        const sede1 = document.getElementById('sede1');
        const sede2 = document.getElementById('sede2');
        sede1.innerHTML = '<option value="">Seleccionar</option>';
        sede2.innerHTML = '<option value="">Seleccionar</option>';
        sedes.forEach(sede => {
            const option1 = document.createElement('option');
            option1.value = sede.nombre; 
            option1.textContent = sede.nombre;
            sede1.appendChild(option1);
            
            const option2 = document.createElement('option');
            option2.value = sede.nombre; 
            option2.textContent = sede.nombre;
            sede2.appendChild(option2);
        });
        
        // Agregar event listeners para evitar selección duplicada
        sede1.addEventListener('change', () => updateSede2Options());
        sede2.addEventListener('change', () => updateSede1Options());
    })
    .catch(error => console.error('Error cargando sedes:', error));
}

function updateSede2Options() {
    const sede1Value = document.getElementById('sede1').value;
    const sede2 = document.getElementById('sede2');
    Array.from(sede2.options).forEach(option => {
        option.disabled = option.value === sede1Value && sede1Value !== '';
    });
}

function updateSede1Options() {
    const sede2Value = document.getElementById('sede2').value;
    const sede1 = document.getElementById('sede1');
    Array.from(sede1.options).forEach(option => {
        option.disabled = option.value === sede2Value && sede2Value !== '';
    });
}
</script>