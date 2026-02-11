<nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top" >
    <div class="container-fluid">
        <div class="d-flex justify-content-center w-100">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="img/logo-metrofem.png" alt="Logo" height="40" class="d-inline-block">
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid mb-5 mt-5">
    <!-- Botón flotante de reinicio -->
    <div id="resetButton" class="floating-reset-btn" style="display: none;">
        <button type="button" 
                class="btn btn-primary btn-lg rounded-circle shadow-lg" 
                onclick="resetForm()"
                data-bs-toggle="popover" 
                data-bs-placement="left" 
                data-bs-content="Nueva búsqueda"
                data-bs-trigger="hover">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>

    <div class="d-flex flex-column align-items-center gap-4">

        <!-- Card de búsqueda principal -->
        <div id="searchCard" class="w-100" style="max-width: 700px;">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-teal-dark text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="bi bi-search me-2"></i>Buscar beneficiario del regalo
                    </h4>
                </div>

                <div class="card-body p-4 w-100">
                    <!-- Formulario de búsqueda -->
                    <form id="searchForm" class="w-100">
                        <div class="mb-4 w-100">
                            <label for="numberId" class="form-label fw-bold text-teal-dark">
                                <i class="bi bi-person-badge me-1"></i>
                                Número de Identificación
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-teal-light border-teal-dark">
                                    <i class="bi bi-hash text-teal-dark"></i>
                                </span>
                                <input type="number"
                                    class="form-control border-teal-dark"
                                    id="numberId"
                                    placeholder="Ingrese número de cédula del beneficiario"
                                    required
                                    maxlength="12"
                                    oninput="this.value = this.value.slice(0, 12);">
                                <button class="btn bg-teal-dark text-white" type="submit">
                                    <i class="bi bi-search me-1"></i>Buscar
                                </button>
                            </div>
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Solo se permiten números
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Loading indicator -->
        <div id="loadingIndicator" class="text-center" style="display: none;">
            <div class="spinner-border text-teal-dark" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-teal-dark fw-bold">Buscando información...</p>
        </div>

        <!-- Card de resultados del beneficiario -->
        <div id="resultArea" class="w-100 mx-auto" style="display: none; max-width: 700px;">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-indigo-dark text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="bi bi-person-fill me-2"></i>
                        Información del beneficiario
                    </h4>
                </div>

                <div class="card-body p-4 w-100">
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <label class="form-label fw-bold text-indigo-dark">
                                <i class="bi bi-person me-1"></i>
                                Nombre Completo
                            </label>
                            <div class="form-control bg-light border-indigo-light" id="userName">--</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-indigo-dark">
                                <i class="bi bi-envelope me-1"></i>
                                Correo Electrónico
                            </label>
                            <div class="form-control bg-light border-indigo-light" id="userEmail">--</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-indigo-dark">
                                <i class="bi bi-building me-1"></i>
                                Empresa
                            </label>
                            <div class="form-control bg-light border-indigo-light" id="userCompany">--</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-indigo-dark">
                                <i class="bi bi-gift me-1"></i>
                                Tipo de regalo
                            </label>
                            <div class="form-control bg-light border-indigo-light" id="userGiftCategory">--</div>
                        </div>

                        <!-- Estado de autorización -->
                        <div class="mb-2">
                            <div id="authorizationStatus" class="alert d-flex align-items-center">
                                <!-- Se llena dinámicamente -->
                            </div>
                        </div>

                        <!-- Botón de acción -->
                        <div class="text-center">
                            <button
                                type="button"
                                id="actionButton"
                                class="btn btn-lg px-4 py-3"
                                style="display: none;">
                                <i class="bi bi-check-circle me-2"></i>
                                Autorizar entrega a tercero
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de autorización a tercero -->
        <div id="authorizationArea" class="w-100 mx-auto" style="display: none; max-width: 800px;">

            <!-- Card separada para la búsqueda del receptor -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header bg-success text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="bi bi-search me-2"></i>Buscar persona autorizada
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form id="receiverSearchForm" class="w-100">
                        <div class="mb-4">
                            <label for="receiverNumberId" class="form-label fw-bold text-success">
                                <i class="bi bi-person-badge me-1"></i>
                                Número de Identificación
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-success-subtle border-success">
                                    <i class="bi bi-hash text-success"></i>
                                </span>
                                <input type="number"
                                    class="form-control border-success"
                                    id="receiverNumberId"
                                    placeholder="Número de cédula de quien recibe"
                                    required
                                    maxlength="12"
                                    oninput="this.value = this.value.slice(0, 12);">
                                <button class="btn btn-success" type="submit">
                                    <i class="bi bi-search me-1"></i>Buscar
                                </button>
                            </div>
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Ingrese el número de identificación de la persona autorizada
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card para información del receptor y documentos -->
            <div id="receiverDataArea" class="card border-0 shadow-lg" style="display: none;">
                <div class="card-header bg-indigo-dark text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="bi bi-person-plus me-2"></i>
                        Completar autorización
                    </h4>
                </div>

                <div class="card-body p-4 w-100">
                    <!-- Documentos requeridos -->
                    <div id="documentsSection" class="w-100">

                        <!-- Información del receptor -->
                        <div id="receiverInfo" class="mb-4">
                            <h5 class="text-success mb-3">
                                <i class="bi bi-person-check me-2"></i>Persona autorizada
                            </h5>
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold">Nombre completo</label>
                                    <div class="form-control bg-light" id="receiverName">--</div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold">Email</label>
                                    <div class="form-control bg-light" id="receiverEmail">--</div>
                                </div>
                            </div>
                        </div>

                        <h5 class="text-indigo-dark mb-3">
                            <i class="bi bi-file-earmark-text me-2"></i>Documentos requeridos
                        </h5>

                        <!-- Firma digital -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-indigo-dark">
                                <i class="bi bi-pen me-1"></i>Firma digital del beneficiario
                            </label>
                            <div class="border border-indigo-dark rounded p-2 bg-light">
                                <canvas id="signatureCanvas" 
                                        width="500" 
                                        height="150" 
                                        style="border: 2px dashed #007bff; cursor: crosshair; width: 100%; max-width: 500px; height: 150px;">
                                    Su navegador no soporta canvas
                                </canvas>
                                <div class="mt-2 text-center">
                                    <button type="button" id="clearSignature" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-trash me-1"></i>Limpiar firma
                                    </button>
                                </div>
                            </div>
                            <div class="form-text">Firme en el recuadro de arriba.</div>
                        </div>

                        
                        <div class="alert bg-indigo-light border-indigo-dark mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-3 text-indigo-dark" style="font-size: 1.5rem;"></i>
                                <div class="text-indigo-dark">
                                    <strong>Importante:</strong> Al confirmar, se generará automáticamente la carta de autorización con los datos y firma proporcionados.
                                </div>
                            </div>
                        </div>

                        <!-- Botón de confirmación -->
                        <div class="text-center">
                            <button type="button" id="confirmAuthorizationBtn" class="btn btn-success btn-lg px-5 py-3">
                                <i class="bi bi-check-circle me-2"></i>
                                Confirmar Autorización
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>

      /* Navbar fixed styling */
    .navbar.fixed-top {
        background-color: #f8f9fa !important;
        z-index: 1040;
    }

    .navbar-brand img {
        transition: transform 0.3s ease;
    }

    .navbar-brand:hover img {
        transform: scale(1.05);
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, var(--bs-teal-dark), var(--bs-indigo-dark)) !important;
    }

    .form-control:focus {
        border-color: var(--bs-teal-dark);
        box-shadow: 0 0 0 0.2rem rgba(0, 109, 104, 0.25);
    }

    .input-group-text {
        border-color: var(--bs-teal-dark);
    }

    .card {
        border-radius: 15px;
        overflow: hidden;
    }

    .card-header {
        border-bottom: 3px solid rgba(255, 255, 255, 0.1);
    }

    .alert {
        border-radius: 10px;
        border: none;
        font-weight: 500;
    }

    .btn {
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

        /* Botón flotante de reinicio */
    .floating-reset-btn {
        position: fixed;
        top: 50%;
        right: 20px;
        transform: translateY(-50%);
        z-index: 1050;
        text-align: center;
    }

    .floating-reset-btn button {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
        border: none;
        background: var(--bs-indigo-dark);
        transition: all 0.3s ease;
    }

    .floating-reset-btn button:hover {
        transform: scale(1.1) rotate(180deg);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        background: var(--bs-indigo-dark) !important;
    }

    /* Animaciones */
    #resultArea,
    #authorizationArea {
        animation: fadeInUp 0.5s ease-out;
    }

    .fade-out {
        animation: fadeOut 0.4s ease-out forwards;
    }

    .fade-in {
        animation: fadeInUp 0.5s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }

    /* Estados */
    .status-available {
        background-color: var(--bs-lime-light) !important;
        color: var(--bs-lime-dark) !important;
        border-left: 4px solid var(--bs-lime-dark);
    }

    .status-authorized {
        background-color: var(--bs-orange-light) !important;
        color: var(--bs-orange-dark) !important;
        border-left: 4px solid var(--bs-orange-dark);
    }

    .status-not-found {
        background-color: var(--bs-red-light) !important;
        color: var(--bs-red-dark) !important;
        border-left: 4px solid var(--bs-red-dark);
    }

    /* Responsive */
    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }

        .card-body {
            padding: 1.5rem !important;
        }

        .w-100 {
            max-width: 100% !important;
        }

        #signatureCanvas {
            height: 120px !important;
        }
    }

    /* Canvas de firma */
    #signatureCanvas {
        touch-action: none;
    }
</style>

<!-- Scripts necesarios -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<!-- Bootstrap 5.3.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<!-- Bootstrap 5.3.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>


<script>
    let currentUser = null;
    let currentReceiver = null;
    let signaturePad = null;

    // Función para reiniciar el formulario
    function resetForm() {
        // Limpiar variables
        currentUser = null;
        currentReceiver = null;
        
        // Ocultar botón de reinicio
        document.getElementById('resetButton').style.display = 'none';
        
        // Limpiar formularios
        document.getElementById('searchForm').reset();
        document.getElementById('receiverSearchForm').reset();
        document.getElementById('numberId').value = '';
        document.getElementById('receiverNumberId').value = '';
        
        // Limpiar firma si existe
        if (signaturePad) {
            signaturePad.clear();
        }
        
        // Ocultar áreas de resultados
        document.getElementById('resultArea').style.display = 'none';
        document.getElementById('authorizationArea').style.display = 'none';
        document.getElementById('receiverDataArea').style.display = 'none';
        
        // Mostrar card de búsqueda con animación
        const searchCard = document.getElementById('searchCard');
        searchCard.classList.remove('fade-out');
        searchCard.style.display = 'block';
        searchCard.classList.add('fade-in');
        
        // Scroll hacia arriba
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Focus en el campo de búsqueda
        setTimeout(() => {
            document.getElementById('numberId').focus();
        }, 300);
    }

    // Inicializar canvas de firma
    function initSignaturePad() {
        const canvas = document.getElementById('signatureCanvas');
        
        if (!canvas) {
            console.error('Canvas no encontrado');
            return;
        }

        // Configurar dimensiones del canvas
        function resizeCanvas() {
            const container = canvas.parentElement;
            const containerWidth = container.offsetWidth - 20; // padding
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            
            // Establecer el tamaño CSS
            canvas.style.width = Math.min(containerWidth, 500) + 'px';
            canvas.style.height = '150px';
            
            // Establecer el tamaño real del canvas
            canvas.width = Math.min(containerWidth, 500) * ratio;
            canvas.height = 150 * ratio;
            
            // Escalar el contexto para el DPI
            const context = canvas.getContext('2d');
            context.scale(ratio, ratio);
            
            // Reinicializar SignaturePad después del redimensionado
            if (signaturePad) {
                signaturePad.off();
            }
            
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
                minWidth: 0.5,
                maxWidth: 2.5,
                throttle: 16,
                minDistance: 5
            });
        }

        // Inicializar
        resizeCanvas();

        // Limpiar firma - remover listener anterior si existe
        const clearBtn = document.getElementById('clearSignature');
        const newClearBtn = clearBtn.cloneNode(true);
        clearBtn.parentNode.replaceChild(newClearBtn, clearBtn);
        
        newClearBtn.addEventListener('click', () => {
            if (signaturePad) {
                signaturePad.clear();
            }
        });

        // Redimensionar en cambio de ventana
        window.addEventListener('resize', resizeCanvas);
        
        console.log('SignaturePad inicializado correctamente');
    }

    // Búsqueda del beneficiario principal
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const numberId = document.getElementById('numberId').value.trim();

        if (!numberId) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Por favor ingrese un número de identificación',
                confirmButtonColor: '#006d68'
            });
            return;
        }

        if (numberId.length < 6) {
            Swal.fire({
                icon: 'warning',
                title: 'Número inválido',
                text: 'El número de identificación debe tener al menos 6 dígitos',
                confirmButtonColor: '#006d68'
            });
            return;
        }

        searchUser(numberId);
    });

    function searchUser(numberId) {
        document.getElementById('loadingIndicator').style.display = 'block';
        document.getElementById('resultArea').style.display = 'none';
        document.getElementById('authorizationArea').style.display = 'none';

        fetch('components/autorizarEntrega/search_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'number_id=' + encodeURIComponent(numberId)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingIndicator').style.display = 'none';

                if (data.success) {
                    displayUserInfo(data.user, data.hasAuthorization || false);
                } else {
                    showNotFoundMessage();
                }
            })
            .catch(error => {
                document.getElementById('loadingIndicator').style.display = 'none';
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor',
                    confirmButtonColor: '#006d68'
                });
            });
    }

    function displayUserInfo(user, hasAuthorization) {
        currentUser = user;

        // Ocultar card de búsqueda con animación
        const searchCard = document.getElementById('searchCard');
        searchCard.classList.add('fade-out');
        
        setTimeout(() => {
            searchCard.style.display = 'none';
            // Mostrar botón de reinicio
            document.getElementById('resetButton').style.display = 'block';
        }, 400);

        document.getElementById('userName').textContent = user.name || '--';
        document.getElementById('userEmail').textContent = user.email || '--';
        document.getElementById('userCompany').textContent = user.company_name || '--';
        document.getElementById('userGiftCategory').textContent = user.gift_category || '--';

        const statusDiv = document.getElementById('authorizationStatus');
        const actionButton = document.getElementById('actionButton');

        if (hasAuthorization) {
            statusDiv.className = 'alert status-authorized d-flex align-items-center';
            statusDiv.innerHTML = `
                <div class="me-3">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h6 class="mb-1">¡Ya tiene autorización registrada!</h6>
                    <small>Este beneficiario ya tiene autorización para entrega a tercero.</small>
                </div>
            `;
            actionButton.style.display = 'none';
        } else {
            statusDiv.className = 'alert status-available d-flex align-items-center';
            statusDiv.innerHTML = `
                <div class="me-3">
                    <i class="bi bi-check-circle-fill" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h6 class="mb-1">¡Disponible para autorización!</h6>
                    <small>Puede autorizar la entrega a un tercero.</small>
                </div>
            `;
            actionButton.className = 'btn btn-success btn-lg px-4 py-3';
            actionButton.style.display = 'inline-block';

            actionButton.onclick = function() {
                showAuthorizationForm();
            };
        }

        document.getElementById('resultArea').style.display = 'block';
    }

    function showNotFoundMessage() {
        document.getElementById('userName').textContent = '--';
        document.getElementById('userEmail').textContent = '--';
        document.getElementById('userCompany').textContent = '--';

        const statusDiv = document.getElementById('authorizationStatus');
        statusDiv.className = 'alert status-not-found d-flex align-items-center';
        statusDiv.innerHTML = `
            <div class="me-3">
                <i class="bi bi-x-circle-fill" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <h6 class="mb-1">Usuario no encontrado</h6>
                <small>No se encontró ningún beneficiario con este número de identificación.</small>
            </div>
        `;

        document.getElementById('resultArea').style.display = 'block';
    }

    function showAuthorizationForm() {
        document.getElementById('authorizationArea').style.display = 'block';
        initSignaturePad();

        // Scroll suave hacia el formulario de autorización
        document.getElementById('authorizationArea').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    // Búsqueda del receptor
    document.getElementById('receiverSearchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const receiverNumberId = document.getElementById('receiverNumberId').value.trim();

        if (!receiverNumberId) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Por favor ingrese el número de identificación del receptor',
                confirmButtonColor: '#198754'
            });
            return;
        }

        if (receiverNumberId === currentUser.number_id.toString()) {
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'El receptor no puede ser la misma persona del beneficiario. Por favor ingrese un número de identificación diferente.',
                confirmButtonColor: '#198754',
                confirmButtonText: 'Entendido'
            });
            // Limpiar el campo
            document.getElementById('receiverNumberId').value = '';
            document.getElementById('receiverNumberId').focus();
            return;
        }

        searchReceiver(receiverNumberId);
    });

    function searchReceiver(receiverNumberId) {
        // Validar que no sea la misma persona
        if (receiverNumberId === currentUser.number_id.toString()) {
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'El receptor no puede ser la misma persona del beneficiario. Por favor ingrese un número de identificación diferente.',
                confirmButtonColor: '#198754',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        fetch('components/autorizarEntrega/search_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'number_id=' + encodeURIComponent(receiverNumberId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Verificar si esta persona ya está autorizada para reclamar por otra persona
                    if (data.isAuthorizedForOther) {
                        const authInfo = data.authorizedForInfo;
                        const authDate = new Date(authInfo.created_at).toLocaleDateString('es-ES');
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Persona ya autorizada',
                            html: `
                                <div class="text-start">
                                    <p><strong>${data.user.name}</strong> ya está autorizada para reclamar un regalo</p>
                                    <p><em>Una persona solo puede estar autorizada para reclamar un regalo a la vez.</em></p>
                                </div>
                            `,
                            confirmButtonColor: '#198754',
                            confirmButtonText: 'Entendido'
                        });
                        
                        // Limpiar el campo
                        document.getElementById('receiverNumberId').value = '';
                        document.getElementById('receiverNumberId').focus();
                        return;
                    }

                    currentReceiver = data.user;
                    document.getElementById('receiverName').textContent = data.user.name;
                    document.getElementById('receiverEmail').textContent = data.user.email || 'No registrado';

                    // Mostrar la card de datos del receptor
                    document.getElementById('receiverDataArea').style.display = 'block';

                    // Importante: Inicializar SignaturePad DESPUÉS de que la card sea visible
                    setTimeout(() => {
                        initSignaturePad();
                    }, 100);

                    // Scroll suave hacia la nueva card
                    document.getElementById('receiverDataArea').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Receptor no encontrado',
                        text: 'No se encontró ningún usuario con este número de identificación',
                        confirmButtonColor: '#198754'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor',
                    confirmButtonColor: '#198754'
                });
            });
    }

    // Confirmar autorización
    document.getElementById('confirmAuthorizationBtn').addEventListener('click', function() {
        if (!currentUser || !currentReceiver) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Faltan datos del beneficiario o receptor',
                confirmButtonColor: '#198754'
            });
            return;
        }

        // Verificación adicional antes del envío
        if (currentUser.number_id === currentReceiver.number_id) {
            Swal.fire({
                icon: 'error',
                title: 'Error de validación',
                text: 'Una persona no puede autorizarse a sí misma para recibir su propio regalo.',
                confirmButtonColor: '#198754'
            });
            return;
        }

        // Validar solo la firma
        if (signaturePad.isEmpty()) {
            Swal.fire({
                icon: 'warning',
                title: 'Firma requerida',
                text: 'Por favor firme en el recuadro',
                confirmButtonColor: '#198754'
            });
            return;
        }

        // Confirmar antes de enviar
        Swal.fire({
            title: 'Confirmar Autorización',
            html: `
                <div class="text-start">
                    <p><strong>Beneficiario:</strong> ${currentUser.name}</p>
                    <p><strong>Tipo de regalo:</strong> ${currentUser.gift_category || 'No especificado'}</p>
                    <p><strong>Autorizado para recibir:</strong> ${currentReceiver.name}</p>
                    <p><strong>Documentos:</strong> ✓ Firma, ✓ Carta (se generará automáticamente)</p>
                    <hr>
                    <div class="alert alert-warning text-start">
                        <p class="mb-1"><strong>Restricciones importantes:</strong></p>
                        <ul class="mb-0">
                            <li>Una persona solo puede estar autorizada para reclamar un regalo a la vez</li>
                            <li>Esta acción no se puede deshacer</li>
                        </ul>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, confirmar autorización',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return submitAuthorization();
            }
        }).then((result) => {
            if (result.isConfirmed && result.value && result.value.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Autorización registrada!',
                    text: 'La autorización ha sido registrada exitosamente. La carta se ha generado automáticamente.',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    resetForm();
                });
            } else if (result.isConfirmed && result.value && !result.value.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al registrar autorización',
                    text: result.value.message,
                    confirmButtonColor: '#198754'
                });
            }
        });
    });

    function submitAuthorization() {
        const formData = new FormData();

        // Datos del beneficiario
        formData.append('beneficiary_number_id', currentUser.number_id);
        formData.append('beneficiary_name', currentUser.name);
        formData.append('beneficiary_gift_category', currentUser.gift_category || 'Regalo');

        // Datos del receptor
        formData.append('receiver_number_id', currentReceiver.number_id);
        formData.append('receiver_name', currentReceiver.name);

        // Solo la firma (ya no se envía foto de identificación)
        const signatureDataURL = signaturePad.toDataURL();
        formData.append('signature', signatureDataURL);

        return fetch('components/autorizarEntrega/save_authorization.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json());
    }

    // Permitir solo números en inputs
    document.getElementById('numberId').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('receiverNumberId').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

        // Inicializar popovers de Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar todos los popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    });
</script>