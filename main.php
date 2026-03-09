<?php
// ===============================
// UTINNOVA-DASHBOARD - main.php
// ===============================
// Este archivo es el punto de entrada principal del dashboard.
// Controla la sesión, verifica el login y carga los componentes visuales.
// También gestiona la alerta de perfil incompleto y la estructura base de la página.

session_start();
include("conexion.php");
// Habilitar la visualización de errores para desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);  // Mostrar todos los errores

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Si no está logueado, redirigir a la página de inicio de sesión
    header('Location: index.php');
    exit;
}
include("components/filters/takeUser.php");

// Obtener información del usuario logueado
$infoUsuario = obtenerInformacionUsuario(); // Obtén la información del usuario
$rol = $infoUsuario['rol'];

// Obtener sedes para Asesor y Administrador
$sedes = [];
$sede_set = isset($_SESSION['sede']);
if ($rol == 'Asesor' || $rol == 'Administrador') {
    $query = mysqli_query($conn, "SELECT nombre FROM sedes");
    while ($row = mysqli_fetch_assoc($query)) {
        if (!empty($row['nombre'])) $sedes[] = $row['nombre'];
    }
}

// Verificar si el usuario tiene campos incompletos en su perfil
$mostrar_alerta = false;
$mensaje_campos = "";

if (isset($_SESSION['campos_incompletos']) && $_SESSION['campos_incompletos'] === true) {
    $mostrar_alerta = true;
    $campos_faltantes = $_SESSION['campos_faltantes'];
    $mensaje_campos = "Por favor complete los siguientes campos en su perfil: ";
    $mensaje_campos .= implode(", ", $campos_faltantes);

    // Limpiar las variables de sesión para no mostrar la alerta en futuras cargas
    unset($_SESSION['campos_incompletos']);
    unset($_SESSION['campos_faltantes']);
}

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <!-- Integración de Bootstrap y DataTables -->
    <!-- Carga de estilos y scripts para la interfaz principal -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/estilo.css?v=0.0.2">
    <link rel="stylesheet" href="css/slidebar.css?v=0.0.3">
    <link rel="stylesheet" href="css/contadores.css?v=0.7">
    <link rel="stylesheet" href="css/dataTables.css?v=0.3">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>ESCOLINK - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="icon" href="img/icono-escolink.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


</head>

<body style="background-color:white">
    <?php include("controller/header.php"); ?> <!-- Barra superior y navegación -->
    <?php include("components/sliderBar.php"); ?> <!-- Menú lateral -->
    <?php include("components/modals/userNew.php"); ?> <!-- Modal para nuevo usuario -->
    <?php include("components/modals/newAdvisor.php"); ?> <!-- Modal para nuevo asesor -->
    <br><br>
</body>
<div style="margin-top: 20px;">
    <div class="mt-3">
        <div id="dashboard">
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0"><i class="bi bi-speedometer2"></i> Dashboard</h2>
                <div class="loader">
                    <!-- Animación de carga -->
                    <div class="slider" style="--i:0"></div>
                    <div class="slider" style="--i:1"></div>
                    <div class="slider" style="--i:2"></div>
                    <div class="slider" style="--i:3"></div>
                    <div class="slider" style="--i:4"></div>
                </div>
            </div>
            <hr>
            <?php include("components/cardContadores/contadoresCards.php"); 
            ?>

            <?php //include("components/aceptUsers/updateStatus.php");  
            ?>
            <div class="row">
                <div class="col-sm-12 col-md-3 col-lg-3">
                    <!-- Espacio para gráficos o widgets adicionales -->
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                    <?php //include("components/graphics/stratum.php");  
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include("controller/footer.php"); ?>
<?php //include("controller/botonFlotanteDerecho.php"); 
?>
<?php include("components/sliderBarBotton.php"); ?>
<!-- Scripts de Bootstrap, DataTables y personalizaciones -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>
<!-- <script src="js/real-time-update-contadores.js?v=0.3"></script> -->
<script src="js/dataTables.js?v=0.2"></script>
<script>
    $(document).ready(function() {
        $('#link-dashboard').addClass('pagina-activa');

        $('#listaInscritos').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            pagingType: "simple"
        });

        // Mostrar Swal para seleccionar sede si es Asesor o Administrador
        var rol = '<?php echo $rol; ?>';
        var sedes = <?php echo json_encode($sedes); ?>;
        var sede_set = <?php echo $sede_set ? 'true' : 'false'; ?>;

        // if ((rol === 'Asesor' || rol === 'Administrador') && sedes.length > 0 && !sede_set) {
        //     let optionsSede = '<option value="">Selecciona una sede</option>';
        //     sedes.forEach(function(sede) {
        //         optionsSede += '<option value="' + sede + '">' + sede + '</option>';
        //     });

        //     Swal.fire({
        //         title: '¿En qué sede vas a trabajar hoy?',
        //         html: `
        //             <div class="container-fluid">
        //                 <div class="row mb-3">
        //                     <div class="col-12">
        //                         <label class="form-label fw-bold">Sede:</label>
        //                         <select id="sedeSelector" class="form-control">
        //                             ${optionsSede}
        //                         </select>
        //                     </div>
        //                 </div>
        //             </div>
        //         `,
        //         showCancelButton: false,
        //         allowOutsideClick: false,
        //         confirmButtonText: 'Seleccionar',
        //         preConfirm: () => {
        //             const sede = document.getElementById('sedeSelector').value;

        //             if (!sede) {
        //                 Swal.showValidationMessage('Debes seleccionar una sede');
        //                 return false;
        //             }

        //             const formData = new FormData();
        //             formData.append('sede', sede);

        //             return fetch('controller/setSede.php', {
        //                 method: 'POST',
        //                 body: formData
        //             }).then(response => response.json());
        //         }
        //     }).then((result) => {
        //         if (result.isConfirmed && result.value.success) {
        //             const sede = document.getElementById('sedeSelector').value;

        //             // Actualizar el campo en el header
        //             if (document.getElementById('headerSede')) {
        //                 document.getElementById('headerSede').textContent = sede;
        //             }

        //             Swal.fire({
        //                 title: '¡Configuración Guardada!',
        //                 html: `
        //                     <div class="text-center">
        //                         <p><strong>Sede:</strong> ${sede}</p>
        //                     </div>
        //                 `,
        //                 icon: 'success',
        //                 timer: 3000,
        //                 timerProgressBar: true
        //             });
        //         } else {
        //             Swal.fire('Error', 'No se pudo guardar la configuración', 'error').then(() => {
        //                 location.reload();
        //             });
        //         }
        //     });
        // }

        // Mostrar alerta si hay campos incompletos en el perfil del usuario
        <?php if ($mostrar_alerta): ?>
            Swal.fire({
                title: 'Perfil incompleto',
                html: '<div style="text-align: center;"><p><strong><?php echo $mensaje_campos; ?></strong></p></div>',
                icon: 'warning',
                allowOutsideClick: false,
                customClass: {
                    popup: 'swal-wide'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'profile.php';
                }
            });
        <?php endif; ?>
    });
</script>

<style>
    .swal-wide {
        width: 600px !important;
    }

    .loader {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: row;
        transform: scale(0.5);
    }

    .slider {
        overflow: hidden;
        background-color: white;
        margin: 0 15px;
        height: 80px;
        width: 20px;
        border-radius: 30px;
        box-shadow: 15px 15px 20px rgba(0, 0, 0, 0.1), -15px -15px 30px #fff,
            inset -5px -5px 10px rgba(0, 0, 255, 0.1),
            inset 5px 5px 10px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .slider::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        height: 20px;
        width: 20px;
        border-radius: 100%;
        box-shadow: inset 0px 0px 0px rgba(0, 0, 0, 0.3), 0px 420px 0 400px #66cc00,
            inset 0px 0px 0px rgba(0, 0, 0, 0.1);
        animation: animate_2 5s ease-in-out infinite;
        animation-delay: calc(-1s * var(--i));
    }

    @keyframes animate_2 {
        0% {
            transform: translateY(250px);
            filter: hue-rotate(0deg);
        }

        50% {
            transform: translateY(0);
        }

        100% {
            transform: translateY(250px);
            filter: hue-rotate(180deg);
        }
    }
</style>

</body>

</html>