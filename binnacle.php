<?php
include("head.php");

$users = [];
$query = "SELECT binnacle.*, action.description, user.name FROM binnacle INNER JOIN action ON binnacle.action = action.action_id LEFT JOIN user ON binnacle.user = user.user_id ORDER BY binnacle.binnacle_id;";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}
?>

<link rel="stylesheet" type="text/css" href="CSS/h1-style.css">
<div style="text-align: center; padding: 50px 50px;">
    <h2 class="h2-style">Bitacora</h2>

    <?php if (!empty($message)): ?>
        <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <table id="table">
        <thead>
            <tr>
                <th>Accion</th>
                <th>Fecha</th>
                <th class="actions">Encargado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $binnacle): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($binnacle['description']); ?></td>
                        <td><?php echo htmlspecialchars($binnacle['time']); ?></td>
                        <td><?php echo htmlspecialchars($binnacle['name']); ?></td>


                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No se encontraron usuarios</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
  <script>
    // Inicializar DataTables con configuración en español y estilos
    $(document).ready(function() {
        $('#table').DataTable({
            "language": {
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    "",
                "sSearch":         "Buscar:",
                "sUrl":            "",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            },
            "pageLength": 10,
            "order": [[ 1, "desc" ]], // Ordenar por fecha descendente por defecto
            "responsive": true
        });
    });
</script>
</div>
</body>
</html>