<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
$extraRol = $infoUsuario['extra_rol']; // Obtener el extra_rol del usuario

$disableConfirm = in_array($rol, ['Control maestro']);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

<div>
    <!-- Card de búsqueda -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-indigo-dark text-white text-center">
                    <h5 class="mb-0">Búsqueda por número de identificación</h5>
                </div>
                <div class="card-body">
                    <div class="row w-100">
                        <div class="col-12 col-md-8">
                            <input type="number" id="searchNumberId" class="form-control form-control-lg border-indigo-dark" placeholder="Ingresa el número de identificación" maxlength="10" oninput="this.value = this.value.slice(0, 10);">
                        </div>
                        <div class="col-12 col-md-4 mt-2 mt-md-0">
                            <button type="button" id="btnBuscar" class="btn bg-indigo-dark text-white btn-lg w-100"><i class="fas fa-search me-2"></i>Buscar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card de resultados -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-lg border-teal-dark" id="resultCard" style="display: none;">
                <div class="card-header d-flex justify-content-between align-items-center bg-teal-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Información del Usuario</h5>
                    <div>
                        <button type="button" id="btnEditar" class="btn btn-outline-light btn-sm me-2">
                            <i class="fas fa-edit me-1"></i>Editar
                        </button>
                        <button type="button" id="btnGuardar" class="btn btn-success btn-sm me-2" style="display: none;">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                        <button type="button" id="btnVerEntrega" class="btn btn-outline-light btn-sm me-2" style="display: none;">
                            <i class="fas fa-eye me-1"></i>Ver Entrega
                        </button>
                        <button type="button" id="btnReenviarCorreo" class="btn btn-outline-light btn-sm me-2" style="display: none;">
                            <i class="fas fa-envelope me-1"></i>Reenviar Correo
                        </button>
                        <button type="button" id="btnConfirmarEntrega" class="btn btn-success btn-sm">
                            <i class="fas fa-check me-1"></i>Confirmar Entrega
                        </button>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <input type="hidden" id="originalNumberId">
                    <div class="container-fluid">
                        <!-- Primera fila: Información básica -->
                        <div class="row mb-4 p-3 bg-white rounded shadow-sm">
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-bold text-teal-dark"><i class="fas fa-id-card me-1"></i> Número de ID:</label>
                                <input type="number" id="resultNumberId" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold text-teal-dark"><i class="fas fa-user-tag me-1"></i> Nombre:</label>
                                <input type="text" id="resultName" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-birthday-cake me-1"></i> Fecha de Nacimiento:</label>
                                <input type="date" id="resultBirthdate" class="form-control border-teal-dark" readonly>
                            </div>
                        </div>

                        <!-- Segunda fila: Contacto -->
                        <div class="row mb-4 p-3 bg-white rounded shadow-sm">
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-phone me-1"></i> Celular:</label>
                                <input type="text" id="resultCellPhone" class="form-control border-teal-dark" readonly maxlength="10" pattern="[0-9]{10}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-envelope me-1"></i> Email:</label>
                                <input type="email" id="resultEmail" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-building me-1"></i> Empresa:</label>
                                <input type="text" id="resultCompany" class="form-control border-teal-dark" readonly>
                            </div>
                        </div>

                        <!-- Tercera fila: Ubicación y datos personales -->
                        <div class="row mb-4 p-3 bg-white rounded shadow-sm">
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-map-marker-alt me-1"></i> Dirección:</label>
                                <input type="text" id="resultAddress" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-city me-1"></i> Ciudad:</label>
                                <input type="text" id="resultCity" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-venus-mars me-1"></i> Género:</label>
                                <select id="resultGender" class="form-control border-teal-dark" disabled>
                                    <option value="MUJER">Mujer</option>
                                    <option value="HOMBRE">Hombre</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-calendar-alt me-1"></i> Fecha de Registro:</label>
                                <input type="date" id="resultRegistrationDate" class="form-control border-teal-dark" readonly>
                            </div>
                        </div>

                        <!-- Cuarta fila: Información de regalos -->
                        <div class="row mb-4 p-3 bg-white rounded shadow-sm">
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-map-marker-alt me-1"></i> Sede:</label>
                                <input type="text" id="resultSede" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-gift me-1"></i> Categoría Regalo:</label>
                                <input type="text" id="resultGiftCategory" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-venus-mars me-1"></i> Género Regalo:</label>
                                <select id="resultGiftGender" class="form-control border-teal-dark" disabled>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="F">Femenino</option>
                                    <option value="M">Masculino</option>
                                    <option value="N">Neutro</option>
                                    <option value="NA">No Aplica</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para información de entrega -->
<div class="modal fade" id="deliveryModal" tabindex="-1" aria-labelledby="deliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white position-relative overflow-hidden">
                <h5 class="modal-title position-relative" id="deliveryModalLabel">
                    <i class="fas fa-gift me-2"></i>
                    Información de Entrega de Regalo
                </h5>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="container-fluid">
                    <!-- Información Principal de Entrega -->
                    <div class="card border-0 shadow-sm mb-4 bg-light">
                        <div class="card-body">
                            <div class="vstack gap-3">
                                <h6 class="card-title text-black mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Detalles de la Entrega
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Fecha de Entrega:</strong></p>
                                        <p class="text-muted" id="modalDeliveryDate">--</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Entregado por:</strong></p>
                                        <p class="text-muted" id="modalDeliveredBy">--</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Sede de Entrega:</strong></p>
                                        <p class="text-muted" id="modalDeliverySede">--</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Tipo de Entrega:</strong></p>
                                        <p class="text-muted" id="modalDeliveryTipoEntrega">--</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Firma Digital -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body signature-container">
                            <div class="vstack gap-2">
                                <h6 class="card-title text-black mb-0">
                                    <i class="fas fa-signature me-2"></i>
                                    Firma Digital
                                </h6>
                                <div class="text-center">
                                    <img id="modalDeliverySignature" src="" alt="Firma" class="img-fluid border rounded" style="max-height: 150px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Foto de Identificación -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="vstack gap-2">
                                <h6 class="card-title text-black mb-0">
                                    <i class="fas fa-camera me-2"></i>
                                    Foto de Identificación
                                </h6>
                                <div class="text-center">
                                    <img id="modalIdPhoto" src="" alt="Foto ID" class="img-fluid border rounded" style="max-height: 200px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Receptor -->
                    <div id="modalRecipientInfo" class="card border-0 shadow-sm" style="display: none;">
                        <div class="card-body">
                            <div class="vstack gap-3">
                                <h6 class="card-title text-black mb-0">
                                    <i class="fas fa-user-friends me-2"></i>
                                    Información del Receptor
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Número de ID del Receptor:</strong></p>
                                        <p class="text-muted" id="modalRecipientNumber">--</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Nombre del Receptor:</strong></p>
                                        <p class="text-muted" id="modalRecipientName">--</p>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" id="modalBtnShowCarta" class="btn bg-teal-dark text-white btn-sm">
                                        <i class="fas fa-file-alt me-1"></i>
                                        Ver Carta de Autorización
                                    </button>
                                </div>
                                <div id="modalRecipientMessages">
                                    <!-- Los mensajes se cargarán dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer bg-light border-top-0">
            <div class="text-muted small w-100 text-center">
                <i class="fas fa-shield-alt me-1"></i>
                Información verificada y registrada en el sistema
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(45deg, var(--bs-teal-dark), var(--bs-indigo-dark)) !important;
    }

    .signature-container {
        transition: all 0.3s ease;
    }

    .signature-container:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    .modal-body .card {
        transition: all 0.3s ease;
    }

    .modal-body .card:hover {
        transform: translateY(-1px);
    }

    #modalRecipientMessages .alert {
        border: none;
        border-radius: 12px;
        font-weight: 500;
    }

    .bg-warning-subtle {
        background-color: #fff3cd !important;
    }

    .border-warning {
        border-color: #ffc107 !important;
        border-width: 2px !important;
    }

    /* Efecto de pulse para campos editables */
    .border-warning {
        animation: pulse-warning 2s infinite;
    }

    @keyframes pulse-warning {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        }
    }

    .pulse-yellow {
        animation: pulse-yellow 2s infinite;
        background-color: rgba(255, 255, 0, 0.1);
        border-color: #ffc107 !important;
    }

    @keyframes pulse-yellow {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 255, 0, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 255, 0, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 255, 0, 0);
        }
    }

    .pulse-green {
        animation: pulse-green 2s infinite;
        background-color: rgba(0, 255, 0, 0.1);
        border-color: #28a745 !important;
    }

    @keyframes pulse-green {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    /* Estilos adicionales para los modales mejorados */
    .swal-container-custom {
        z-index: 10000;
    }

    .swal-letter-modal {
        z-index: 10500 !important;
    }

    .signature-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    #signatureCanvas {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease;
    }

    #signatureCanvas:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }

    /* Estilos adicionales para los modales mejorados */
    .swal-container-custom {
        z-index: 10000;
    }

    .swal-letter-modal {
        z-index: 10500 !important;
    }

    .signature-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    #signatureCanvas {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease;
    }

    #signatureCanvas:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }

    /* Estilos existentes... */
    .bg-gradient-primary {
        background: linear-gradient(45deg, var(--bs-teal-dark), var(--bs-indigo-dark)) !important;
    }

    .signature-container {
        transition: all 0.3s ease;
    }

    .signature-container:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    .modal-body .card {
        transition: all 0.3s ease;
    }

    .modal-body .card:hover {
        transform: translateY(-1px);
    }

    #modalRecipientMessages .alert {
        border: none;
        border-radius: 12px;
        font-weight: 500;
    }

    .bg-warning-subtle {
        background-color: #fff3cd !important;
    }

    .border-warning {
        border-color: #ffc107 !important;
        border-width: 2px !important;
    }

    /* Efecto de pulse para campos editables */
    .border-warning {
        animation: pulse-warning 2s infinite;
    }

    @keyframes pulse-warning {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        }
    }

    .pulse-yellow {
        animation: pulse-yellow 2s infinite;
        background-color: rgba(255, 255, 0, 0.1);
        border-color: #ffc107 !important;
    }

    @keyframes pulse-yellow {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 255, 0, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 255, 0, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 255, 0, 0);
        }
    }

    .pulse-green {
        animation: pulse-green 2s infinite;
        background-color: rgba(0, 255, 0, 0.1);
        border-color: #28a745 !important;
    }

    @keyframes pulse-green {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    /* Estilos para campos en edición */
    .pulse-yellow {
        animation: pulse-yellow 2s infinite;
        background-color: rgba(255, 255, 0, 0.1) !important;
        border-color: #ffc107 !important;
        border-width: 2px !important;
    }

    .pulse-green {
        animation: pulse-green 2s infinite;
        background-color: rgba(0, 255, 0, 0.1) !important;
        border-color: #28a745 !important;
        border-width: 2px !important;
    }

    @keyframes pulse-yellow {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 255, 0, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 255, 0, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 255, 0, 0);
        }
    }

    @keyframes pulse-green {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    /* Campos readonly mantienen su estilo base */
    input[readonly],
    select[disabled] {
        background-color: #f8f9fa !important;
        border-color: #ced4da !important;
    }

    /* Campos editables tienen un estilo destacado */
    input:not([readonly]),
    select:not([disabled]) {
        font-weight: 500;
    }

    input:not([readonly]):focus,
    select:not([disabled]):focus {
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
        border-color: #28a745 !important;
    }
</style>

<!-- Incluir Signature Pad para firma digital -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
    let currentDeliveries = [];

    document.getElementById('btnBuscar').addEventListener('click', function() {
        const numberId = document.getElementById('searchNumberId').value.trim();
        if (!numberId || isNaN(numberId)) {
            Swal.fire('Error', 'Ingresa un número de ID válido.', 'error');
            return;
        }

        fetch('components/individualSearch/getData.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'number_id=' + encodeURIComponent(numberId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Llenar los campos con la nueva estructura
                    document.getElementById('resultNumberId').value = data.data.number_id;
                    document.getElementById('originalNumberId').value = data.data.number_id;
                    document.getElementById('resultName').value = data.data.name;
                    document.getElementById('resultBirthdate').value = data.data.birthdate || '';
                    document.getElementById('resultCompany').value = data.data.company_name || '';
                    document.getElementById('resultCellPhone').value = data.data.cell_phone || '';
                    document.getElementById('resultEmail').value = data.data.email || '';
                    document.getElementById('resultAddress').value = data.data.address || '';
                    document.getElementById('resultCity').value = data.data.city || '';
                    document.getElementById('resultRegistrationDate').value = data.data.registration_date || '';
                    document.getElementById('resultGender').value = data.data.gender || '';
                    document.getElementById('resultSede').value = data.data.sede || '';
                    document.getElementById('resultGiftCategory').value = data.data.gift_category || '';
                    document.getElementById('resultGiftGender').value = data.data.gift_gender || '';

                    // Guardar entregas
                    currentDeliveries = data.deliveries || [];
                    window.hasDeliveries = data.has_deliveries;

                    document.getElementById('resultCard').style.display = 'block';
                    document.getElementById('btnGuardar').style.display = 'none';
                    document.getElementById('btnEditar').disabled = false;

                    // Configurar botón de confirmar entrega
                    const btnConfirmar = document.getElementById('btnConfirmarEntrega');

                    // El botón se oculta si:
                    // 1. El usuario tiene rol que deshabilita confirmaciones
                    // 2. Ya tiene una entrega del tipo actual (gift_category + gift_gender)
                    // 3. No tiene tipo de entrega definido
                    const shouldHideButton = <?php echo $disableConfirm ? 'true' : 'false'; ?> ||
                        !data.can_deliver_current_type ||
                        !data.current_delivery_type;

                    if (shouldHideButton) {
                        btnConfirmar.style.display = 'none';
                    } else {
                        btnConfirmar.style.display = 'inline-block';
                        btnConfirmar.disabled = false;
                    }

                    // Configurar botones según entregas
                    if (data.has_deliveries) {
                        document.getElementById('btnVerEntrega').style.display = 'inline-block';
                        document.getElementById('btnReenviarCorreo').style.display = 'inline-block';

                        // Mostrar mensaje informativo si ya tiene entrega del tipo actual
                        if (!data.can_deliver_current_type && data.current_delivery_type) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Entrega ya registrada',
                                text: `Esta persona ya tiene una entrega registrada del tipo: ${data.current_delivery_type}`,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        document.getElementById('btnVerEntrega').style.display = 'none';
                        document.getElementById('btnReenviarCorreo').style.display = 'none';

                        // Si no tiene entregas pero no puede entregar el tipo actual, mostrar mensaje
                        if (!data.can_deliver_current_type) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sin categoría de regalo',
                                text: 'Este usuario no tiene una categoría de regalo válida asignada.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    }
                } else {
                    Swal.fire('Usuario no encontrado', data.message, 'error');
                    document.getElementById('resultCard').style.display = 'none';
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Error en la solicitud: ' + error.message, 'error');
            });
    });

    // AGREGAR: Funcionalidad del botón Editar
    document.getElementById('btnEditar').addEventListener('click', function() {
        const hasDeliveries = window.hasDeliveries;

        if (hasDeliveries) {
            // Edición limitada para usuarios con entregas
            enableLimitedEdit();
        } else {
            // Edición completa para usuarios sin entregas
            enableFullEdit();
        }
    });

    function enableLimitedEdit() {
        // Solo permitir editar celular y email
        const editableFields = ['resultCellPhone', 'resultEmail'];

        editableFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            field.removeAttribute('readonly');
            field.classList.add('pulse-yellow');
            field.style.backgroundColor = '#fff3cd';
        });

        // Cambiar botones
        document.getElementById('btnEditar').style.display = 'none';
        document.getElementById('btnGuardar').style.display = 'inline-block';

        // Mostrar mensaje informativo
        Swal.fire({
            icon: 'info',
            title: 'Edición Limitada',
            text: 'Este usuario tiene entregas registradas, solo puede editar teléfono y email.',
            timer: 3000,
            showConfirmButton: false
        });
    }

    function enableFullEdit() {
        // Permitir editar todos los campos excepto el ID original
        const editableFields = [
            'resultNumberId', 'resultName', 'resultBirthdate', 'resultCompany',
            'resultCellPhone', 'resultEmail', 'resultAddress', 'resultCity',
            'resultGender', 'resultRegistrationDate', 'resultSede',
            'resultGiftCategory', 'resultGiftGender'
        ];

        editableFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field.tagName === 'SELECT') {
                field.removeAttribute('disabled');
            } else {
                field.removeAttribute('readonly');
            }
            field.classList.add('pulse-green');
            field.style.backgroundColor = '#d1edff';
        });

        // Cambiar botones
        document.getElementById('btnEditar').style.display = 'none';
        document.getElementById('btnGuardar').style.display = 'inline-block';

        // Mostrar mensaje informativo
        Swal.fire({
            icon: 'success',
            title: 'Modo Edición',
            text: 'Todos los campos están disponibles para edición.',
            timer: 2000,
            showConfirmButton: false
        });
    }

    // AGREGAR: Funcionalidad del botón Guardar
    document.getElementById('btnGuardar').addEventListener('click', function() {
        const hasDeliveries = window.hasDeliveries;

        if (hasDeliveries) {
            saveLimitedEdit();
        } else {
            saveFullEdit();
        }
    });

    function saveLimitedEdit() {
        const formData = new FormData();
        formData.append('original_number_id', document.getElementById('originalNumberId').value);
        formData.append('cell_phone', document.getElementById('resultCellPhone').value);
        formData.append('email', document.getElementById('resultEmail').value);
        formData.append('limited_edit', 'true');

        saveUserData(formData);
    }

    function saveFullEdit() {
        // Validaciones básicas
        const numberIdField = document.getElementById('resultNumberId');
        const nameField = document.getElementById('resultName');
        const cellPhoneField = document.getElementById('resultCellPhone');
        const emailField = document.getElementById('resultEmail');

        if (!numberIdField.value.trim()) {
            Swal.fire('Error', 'El número de identificación es obligatorio.', 'error');
            numberIdField.focus();
            return;
        }

        if (!nameField.value.trim()) {
            Swal.fire('Error', 'El nombre es obligatorio.', 'error');
            nameField.focus();
            return;
        }

        // Validar email si se proporciona
        if (emailField.value.trim() && !isValidEmail(emailField.value)) {
            Swal.fire('Error', 'El formato del email no es válido.', 'error');
            emailField.focus();
            return;
        }

        // Validar celular si se proporciona
        if (cellPhoneField.value.trim() && !isValidPhone(cellPhoneField.value)) {
            Swal.fire('Error', 'El celular debe tener exactamente 10 dígitos.', 'error');
            cellPhoneField.focus();
            return;
        }

        const formData = new FormData();
        formData.append('original_number_id', document.getElementById('originalNumberId').value);
        formData.append('number_id', numberIdField.value);
        formData.append('name', nameField.value);
        formData.append('birthdate', document.getElementById('resultBirthdate').value);
        formData.append('company_name', document.getElementById('resultCompany').value);
        formData.append('cell_phone', cellPhoneField.value);
        formData.append('email', emailField.value);
        formData.append('address', document.getElementById('resultAddress').value);
        formData.append('city', document.getElementById('resultCity').value);
        formData.append('gender', document.getElementById('resultGender').value);
        formData.append('registration_date', document.getElementById('resultRegistrationDate').value);
        formData.append('sede', document.getElementById('resultSede').value);
        formData.append('gift_category', document.getElementById('resultGiftCategory').value);
        formData.append('gift_gender', document.getElementById('resultGiftGender').value);
        formData.append('limited_edit', 'false');

        saveUserData(formData);
    }

    function saveUserData(formData) {
        // Mostrar loading
        Swal.fire({
            title: 'Guardando...',
            text: 'Actualizando información del usuario',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('components/individualSearch/updateData.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Datos Actualizados!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Deshabilitar campos y cambiar botones
                        disableAllFields();
                        document.getElementById('btnGuardar').style.display = 'none';
                        document.getElementById('btnEditar').style.display = 'inline-block';

                        // Actualizar el originalNumberId si cambió
                        if (data.updated_fields === 'all') {
                            document.getElementById('originalNumberId').value = document.getElementById('resultNumberId').value;
                        }
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Error de conexión: ' + error.message, 'error');
            });
    }

    function disableAllFields() {
        const allFields = [
            'resultNumberId', 'resultName', 'resultBirthdate', 'resultCompany',
            'resultCellPhone', 'resultEmail', 'resultAddress', 'resultCity',
            'resultGender', 'resultRegistrationDate', 'resultSede',
            'resultGiftCategory', 'resultGiftGender'
        ];

        allFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field.tagName === 'SELECT') {
                field.setAttribute('disabled', 'disabled');
            } else {
                field.setAttribute('readonly', 'readonly');
            }
            field.classList.remove('pulse-yellow', 'pulse-green');
            field.style.backgroundColor = '';
        });
    }

    // Funciones de validación
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidPhone(phone) {
        const phoneRegex = /^\d{10}$/;
        return phoneRegex.test(phone);
    }

    // Botón Ver Entrega
    document.getElementById('btnVerEntrega').addEventListener('click', function() {
        if (currentDeliveries.length === 0) {
            Swal.fire('Info', 'No hay entregas registradas para esta persona.', 'info');
            return;
        }

        if (currentDeliveries.length === 1) {
            showDeliveryModal(currentDeliveries[0]);
        } else {
            const deliveryOptions = currentDeliveries.map((delivery, index) => {
                const date = new Date(delivery.reception_date);
                const formattedDate = date.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
                return `<option value="${index}">${delivery.tipo_entrega} - ${formattedDate}</option>`;
            }).join('');

            Swal.fire({
                title: 'Seleccionar Entrega',
                html: `<select id="deliverySelect" class="form-control">${deliveryOptions}</select>`,
                showCancelButton: true,
                confirmButtonText: 'Ver Entrega',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const selectedIndex = document.getElementById('deliverySelect').value;
                    showDeliveryModal(currentDeliveries[selectedIndex]);
                }
            });
        }
    });

    function showDeliveryModal(delivery) {
        let date = new Date(delivery.reception_date);
        document.getElementById('modalDeliveryDate').textContent = date.toLocaleDateString('es-ES');
        document.getElementById('modalDeliveredBy').textContent = delivery.delivered_name || delivery.delivered_by;
        document.getElementById('modalDeliverySede').textContent = delivery.sede || 'N/A';
        document.getElementById('modalDeliveryTipoEntrega').textContent = delivery.tipo_entrega || 'N/A';
        document.getElementById('modalDeliverySignature').src = 'img/firmasRegalos/' + delivery.signature;
        document.getElementById('modalIdPhoto').src = 'uploads/idPhotos/' + delivery.id_photo;

        const userNumberId = document.getElementById('resultNumberId').value;

        if (delivery.recipient_number_id != userNumberId) {
            document.getElementById('modalRecipientInfo').style.display = 'block';
            document.getElementById('modalRecipientNumber').textContent = delivery.recipient_number_id;
            document.getElementById('modalRecipientName').textContent = delivery.recipient_name || 'No registrado';

            if (delivery.authorization_letter && delivery.authorization_letter !== 'N/A') {
                document.getElementById('modalBtnShowCarta').style.display = 'inline-block';
                document.getElementById('modalBtnShowCarta').onclick = () => {
                    window.open('uploads/authorizations/' + delivery.authorization_letter, '_blank');
                };
            } else {
                document.getElementById('modalBtnShowCarta').style.display = 'none';
            }

            document.getElementById('modalRecipientMessages').innerHTML = '';
        } else {
            document.getElementById('modalRecipientInfo').style.display = 'block';
            document.getElementById('modalBtnShowCarta').style.display = 'none';
            document.getElementById('modalRecipientMessages').innerHTML = `
                <div class="alert alert-success d-flex align-items-center shadow-sm">
                    <i class="fas fa-check-circle me-2"></i>
                    <span><strong>Entrega Personal:</strong> El regalo fue entregado directamente al beneficiario.</span>
                </div>
            `;
        }

        const deliveryModal = new bootstrap.Modal(document.getElementById('deliveryModal'));
        deliveryModal.show();
    }

    // Botón Confirmar Entrega - VERSIÓN ACTUALIZADA CON AUTORIZACIÓN PREVIA
    document.getElementById('btnConfirmarEntrega').addEventListener('click', function() {
        const userNumberId = document.getElementById('resultNumberId').value;
        const userName = document.getElementById('resultName').value;

        // Primer modal: ¿Quién recibe?
        Swal.fire({
            title: 'Confirmar Entrega de Regalo',
            html: `
                <div class="container-fluid">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">¿La persona que recibe es la misma del registro?</label>
                            <div class="mt-2">
                                <input type="radio" id="samePersonYes" name="samePerson" value="yes" checked>
                                <label for="samePersonYes" class="me-3">Sí, soy yo</label>
                                <input type="radio" id="samePersonNo" name="samePerson" value="no">
                                <label for="samePersonNo">No, es una tercera persona</label>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Continuar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const samePersonElement = document.querySelector('input[name="samePerson"]:checked');
                if (!samePersonElement) {
                    Swal.showValidationMessage('Debe especificar quién recibe el regalo.');
                    return false;
                }
                return samePersonElement.value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const samePerson = result.value;

                if (samePerson === 'yes') {
                    // Entrega personal - ir directo al modal de firma y foto
                    showPersonalDeliveryModal(userNumberId, userName);
                } else {
                    // Tercera persona - verificar autorización existente
                    checkExistingAuthorization(userNumberId, userName);
                }
            }
        });
    });

    // Función para verificar autorización existente
    function checkExistingAuthorization(userNumberId, userName) {
        // Mostrar loading
        Swal.fire({
            title: 'Verificando autorización...',
            text: 'Buscando autorización previamente registrada',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('components/individualSearch/search_authorization.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'beneficiary_number_id=' + encodeURIComponent(userNumberId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.has_authorization) {
                    // Existe autorización - mostrar datos y pedir solo firma
                    showExistingAuthorizationModal(userNumberId, userName, data.data);
                } else {
                    // No existe autorización - mostrar advertencia y no permitir continuar
                    Swal.fire({
                        icon: 'warning',
                        title: 'Autorización No Registrada',
                        html: `
                        <div class="text-start">
                            <div class="alert alert-danger mb-3">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>No se encontró autorización previa</strong>
                            </div>
                            
                            <p class="mb-3">
                                Para que una tercera persona pueda reclamar el regalo, 
                                el beneficiario debe registrar previamente una autorización.
                            </p>
                            
                            <div class="card bg-light border-info">
                                <div class="card-body">
                                    <h6 class="card-title text-info">
                                        <i class="fas fa-info-circle me-2"></i>¿Cómo registrar la autorización?
                                    </h6>
                                    <ol class="mb-0 ps-3">
                                        <li>El beneficiario debe acceder al módulo de "Autorizar Entrega"</li>
                                        <li>Buscar su número de identificación</li>
                                        <li>Ingresar los datos de la persona autorizada</li>
                                        <li>Subir foto de identificación y firmar digitalmente</li>
                                        <li>El sistema generará automáticamente la carta de autorización</li>
                                    </ol>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning mt-3 mb-0">
                                <small>
                                    <i class="fas fa-shield-alt me-1"></i>
                                    <strong>Importante:</strong> Solo se permite una autorización por beneficiario y 
                                    una persona solo puede estar autorizada para reclamar un regalo a la vez.
                                </small>
                            </div>
                        </div>
                    `,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#0d6efd',
                        width: '600px'
                    });
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Error al verificar autorización: ' + error.message, 'error');
            });
    }

    // Modal para entrega con autorización existente - MEJORADO
    function showExistingAuthorizationModal(userNumberId, userName, authData) {
        const formattedDate = new Date(authData.created_at).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });

        Swal.fire({
            title: '<i class="fas fa-check-circle text-success me-2"></i>Autorización Registrada',
            html: `
                <div class="container-fluid">
                    <div class="alert alert-success border-success mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-check me-3" style="font-size: 2rem;"></i>
                            <div class="text-start">
                                <strong class="d-block">Autorización Válida Encontrada</strong>
                                <small class="text-muted">Registrada el ${formattedDate}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-indigo-dark shadow-sm mb-4">
                        <div class="card-header bg-indigo-dark text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-user-check me-2"></i>Persona Autorizada para Recibir
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small">
                                        <i class="fas fa-id-card me-1"></i>Número de Identificación
                                    </label>
                                    <div class="form-control bg-light border-0">${authData.receiver_number_id}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small">
                                        <i class="fas fa-user me-1"></i>Nombre Completo
                                    </label>
                                    <div class="form-control bg-light border-0">${authData.receiver_name}</div>
                                </div>
                                ${authData.receiver_company ? `
                                <div class="col-12">
                                    <label class="form-label fw-bold text-muted small">
                                        <i class="fas fa-building me-1"></i>Empresa
                                    </label>
                                    <div class="form-control bg-light border-0">${authData.receiver_company}</div>
                                </div>
                                ` : ''}

                                ${authData.authorization_letter ? `
                            <div class="text-center mt-3">
                                <button type="button" id="btnViewAuthLetter" class="btn bg-teal-dark text-white btn-sm">
                                    <i class="fas fa-file-pdf me-2"></i>Ver Carta de Autorización
                                </button>
                            </div>
                            ` : ''}
                            </div>
                        </div>
                    </div>

                    <div class="card border-warning shadow-sm mb-3">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-camera me-2"></i>Foto de Identificación Requerida
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="vstack gap-2">
                                <p class="text-muted small mb-2 text-center">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Adjunte una foto de la cédula o documento de identificación de la persona que recibe el regalo.
                                </p>
                                <input type="file" id="photoFile" class="form-control" accept="image/*" required>
                                <small class="form-text text-muted text-center">Formatos permitidos: JPG, PNG, GIF</small>
                            </div>
                        </div>
                    </div>

                    <div class="card border-teal-dark shadow-sm mb-3">
                        <div class="card-header bg-teal-dark text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-signature me-2"></i>Firma de Recepción Requerida
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="vstack gap-2">
                                <p class="text-muted small mb-2 text-center">
                                    <i class="fas fa-info-circle me-1"></i>
                                    La persona autorizada debe firmar en el siguiente recuadro para confirmar la recepción del regalo.
                                </p>
                                <div class="signature-wrapper p-3 bg-light rounded border">
                                    <canvas id="signatureCanvas" width="600" height="200" class="bg-white rounded w-100" style="touch-action: none; cursor: crosshair;">
                                    </canvas>
                                </div>
                                <div class="text-center mt-2">
                                    <button type="button" id="clearSignature" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eraser me-1"></i>Limpiar Firma
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert bg-indigo-light border-indigo-dark text-indigo-dark mb-0">
                        <small>
                            <i class="fas fa-lock me-1"></i>
                            Esta información es confidencial y está protegida por el sistema.
                        </small>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check-circle me-2"></i>Confirmar Entrega',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Cancelar',
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            width: '750px',
            customClass: {
                container: 'swal-container-custom'
            },
            didOpen: () => {
                // Inicializar SignaturePad
                const canvas = document.getElementById('signatureCanvas');
                const signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255,255,255,0)',
                    penColor: 'rgb(0, 0, 0)',
                    minWidth: 1,
                    maxWidth: 3
                });

                document.getElementById('clearSignature').addEventListener('click', () => {
                    signaturePad.clear();
                });

                window.signaturePad = signaturePad;

                // Botón para ver carta de autorización
                if (authData.authorization_letter) {
                    document.getElementById('btnViewAuthLetter').addEventListener('click', () => {
                        showAuthorizationLetterModal(authData.authorization_letter);
                    });
                }
            },
            preConfirm: () => {
                // Validar foto de identificación
                const photoFile = document.getElementById('photoFile').files[0];
                if (!photoFile) {
                    Swal.showValidationMessage('Por favor adjunte la foto de identificación');
                    return false;
                }

                // Validar firma
                if (window.signaturePad.isEmpty()) {
                    Swal.showValidationMessage('Por favor firme en el recuadro');
                    return false;
                }

                const ctx = window.signaturePad.canvas.getContext('2d');
                ctx.globalCompositeOperation = 'destination-over';
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, window.signaturePad.canvas.width, window.signaturePad.canvas.height);

                const signatureDataURL = window.signaturePad.toDataURL();

                const formData = new FormData();
                formData.append('user_number_id', userNumberId);
                formData.append('recipient_number_id', authData.receiver_number_id);
                formData.append('recipient_name', authData.receiver_name);
                formData.append('signature', signatureDataURL);
                formData.append('id_photo', photoFile);
                formData.append('use_existing_auth', 'true');
                formData.append('authorization_id', authData.id);

                return fetch('components/individualSearch/confirmDelivery.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json());
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const data = result.value;
                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? '¡Entrega Confirmada!' : 'Error',
                    text: data.message,
                    timer: data.success ? 2000 : undefined,
                    showConfirmButton: !data.success
                });

                if (data.success) {
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            }
        });
    }

    // Nueva función para mostrar la carta de autorización en un modal secundario
    function showAuthorizationLetterModal(letterFilename) {
        const letterUrl = 'uploads/authorizations/' + letterFilename;

        Swal.fire({
            title: '<i class="fas fa-file-contract me-2"></i>Carta de Autorización',
            html: `
                <div style="width: 100%; height: 600px; border: 2px solid #dee2e6; border-radius: 8px; overflow: hidden;">
                    <iframe 
                        src="${letterUrl}" 
                        style="width: 100%; height: 100%; border: none;"
                        title="Carta de Autorización">
                    </iframe>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        Esta carta fue generada automáticamente por el sistema cuando se registró la autorización
                    </small>
                </div>
            `,
            width: '900px',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="fas fa-download me-2"></i>Descargar',
            confirmButtonColor: '#0d6efd',
            showCancelButton: true,
            cancelButtonText: '<i class="fas fa-arrow-left me-2"></i>Volver',
            cancelButtonColor: '#6c757d',
            customClass: {
                container: 'swal-letter-modal'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Descargar el archivo
                window.open(letterUrl, '_blank');
            }
        });
    }

    // Modal para entrega personal
    function showPersonalDeliveryModal(userNumberId, userName) {
        Swal.fire({
            title: 'Entrega Personal',
            html: `
                <div class="container-fluid">
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-user-check me-2"></i>
                        <strong>Entrega personal a:</strong> ${userName}
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Foto de identificación:</label>
                            <input type="file" id="photoFile" class="form-control" accept="image/*" required>
                            <small class="text-muted">Foto de la cédula o documento de identidad</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="form-label fw-bold">Firma de recepción:</label>
                            <small class="d-block text-muted mb-2">Confirme con su firma que recibió el regalo</small>
                            <canvas id="signatureCanvas" width="600" height="200" class="border rounded w-100" style="touch-action: none;"></canvas>
                            <button type="button" id="clearSignature" class="btn btn-sm btn-outline-secondary mt-2">Limpiar Firma</button>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Confirmar Entrega',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            width: '600px',
            didOpen: () => {
                const canvas = document.getElementById('signatureCanvas');

                // Función para redimensionar el canvas correctamente
                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    const rect = canvas.getBoundingClientRect();
                    canvas.width = rect.width * ratio;
                    canvas.height = rect.height * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);
                    canvas.style.width = rect.width + 'px';
                    canvas.style.height = rect.height + 'px';
                }

                // Redimensionar canvas
                resizeCanvas();

                const signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255,255,255,0)',
                    penColor: 'rgb(0, 0, 0)',
                    minWidth: 1,
                    maxWidth: 3
                });

                document.getElementById('clearSignature').addEventListener('click', () => {
                    signaturePad.clear();
                });

                window.signaturePad = signaturePad;
            },
            preConfirm: () => {
                const photoFile = document.getElementById('photoFile').files[0];

                if (!photoFile) {
                    Swal.showValidationMessage('Debe tomar/subir una foto de identificación.');
                    return false;
                }

                if (window.signaturePad.isEmpty()) {
                    Swal.showValidationMessage('Debe firmar para confirmar la recepción.');
                    return false;
                }

                const ctx = window.signaturePad.canvas.getContext('2d');
                ctx.globalCompositeOperation = 'destination-over';
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, window.signaturePad.canvas.width, window.signaturePad.canvas.height);

                const signatureDataURL = window.signaturePad.toDataURL();

                const formData = new FormData();
                formData.append('user_number_id', userNumberId);
                formData.append('recipient_number_id', userNumberId);
                formData.append('recipient_name', userName);
                formData.append('signature', signatureDataURL);
                formData.append('id_photo', photoFile);
                formData.append('use_existing_auth', 'false');

                return fetch('components/individualSearch/confirmDelivery.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json());
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const data = result.value;
                Swal.fire({
                    title: data.success ? '¡Entrega Confirmada!' : 'Error',
                    text: data.message,
                    icon: data.success ? 'success' : 'error',
                    timer: data.success ? 2000 : undefined,
                    showConfirmButton: !data.success
                });

                if (data.success) {
                    setTimeout(() => {
                        location.reload();
                    }, 800);
                }
            }
        });
    }
</script>