<?php
include("conexion.php");
// Habilitar la visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);  // Mostrar todos los errores
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- Integración de jquery para lectura en tiempo real -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Integración de Bootstrap y DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/contadores.css?v=0.7">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Autorizar tercero</title>
    <link rel="icon" href="img/gift_flow_icono.png" type="image/x-icon">
</head>

<body style="background-color:#f8f9fa">
    <?php //include("controller/header.php"); ?>
    <?php //include("components/sliderBar.php"); ?>
    <?php //include("components/modals/userNew.php"); ?>
    <br>
    <?php include "components/autorizarEntrega/auto_form.php"; ?>


</body>

<?php 
//include "components/autorizarEntrega/auto_form.php";
include("controller/footer.php"); ?>
<?php //include("controller/botonFlotanteDerecho.php"); ?>
<?php //include("components/sliderBarBotton.php"); ?>

<!-- Scripts de Bootstrap, DataTables y personalizaciones -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="js/dataTables.js?v=0.2"></script>
<script src="node_modules/sortablejs/Sortable.min.js"></script> -->

<script>
    // $(document).ready(function() {
    //     $('#link-dashboard').addClass('pagina-activa');

    //     // Inicialización de DataTable
    //     $('#listaInscritos').DataTable({
    //         responsive: true,
    //         language: {
    //             url: "controller/datatable_esp.json"
    //         },
    //         pagingType: "simple"
    //     });
    // });
</script>

</body>

</html>