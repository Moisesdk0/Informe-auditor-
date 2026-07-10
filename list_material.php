<?php
include("head.php");

// Procesar eliminación de material
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $delete_stmt = $conn->prepare("DELETE FROM material WHERE material_id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    
    if ($delete_stmt->execute()) {
        // Registrar en bitácora
        $time = date('Y-m-d H:i:s');
        $action = 14; // Acción para eliminar material
        $insert_stmt = $conn->prepare("INSERT INTO binnacle (time, action, user) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sii", $time, $action, $id);
        $insert_stmt->execute();
        
        // Usar sesión para pasar el mensaje
        $_SESSION['message'] = "Material eliminado correctamente";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error al eliminar el material: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
 
}

// Procesar cambio de estado activo/inactivo
if (isset($_GET['toggle_status'])) {
    $material_id = intval($_GET['toggle_status']);
    
    // Obtener estado actual
    $result = mysqli_query($conn, "SELECT estatus FROM material WHERE material_id = $material_id");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $new_status = $row['estatus'] ? 0 : 1; // Cambiar el estado
        
        // Actualizar estado
        $update = mysqli_query($conn, "UPDATE material SET estatus = $new_status WHERE material_id = $material_id");
        
        if ($update) {
            // Registrar en bitácora
            $time = date('Y-m-d H:i:s');
            $action = $new_status ? 4 : 5; // 4=Activación, 5=Desactivación
            $id ?? 0;
            mysqli_query($conn, "INSERT INTO binnacle (time, action, user) VALUES ('$time', $action, $id)");
            
            $_SESSION['message'] = "Estado del material actualizado correctamente";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error al actualizar el estado: " . mysqli_error($conn);
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Material no encontrado";
        $_SESSION['message_type'] = "error";
    }
  
}

// Obtener mensaje de la sesión si existe
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    // Limpiar los mensajes de la sesión
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// Obtener lista de todos los materiales
$materiales = [];
$query = "SELECT material.*, categoria.details, unidad.siglas 
          FROM material 
          INNER JOIN unidad ON material.unidad = unidad.unidad_id 
          INNER JOIN categoria ON material.categoria = categoria.categoria_id";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $materiales[] = $row;
    }
}
?>
<link rel="stylesheet" type="text/css" href="CSS/h1-style.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<!-- DataTables Bootstrap 5 CSS (para mejor estilo) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<!-- DataTables Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- jQuery (necesario para DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<!-- DataTables Bootstrap 5 JS -->
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .btn {
        padding: 5px 10px;
        text-decoration: none;
        border-radius: 3px;
        font-size: 14px;
        margin: 2px;
    }
    .btn-edit {
        background-color: #4CAF50;
        color: white;
    }
    .btn-status {
        color: white;
    }
    .btn-active {
        background-color: rgb(43, 82, 255);
    }
    .btn-inactive {
        background-color: #ff9800;
    }
    .btn-danger {
        background-color: #f44336;
        color: white;
    }
    .btn-success {
        background-color: #4CAF50;
        color: white;
    }
    .btn-warning {
        background-color: #ff9800;
        color: white;
    }
    .btn-add {
        background-color: #2196F3;
        color: white;
        padding: 8px 15px;
        display: inline-block;
        margin-bottom: 20px;
    }
    .status-badge {
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 12px;
        color: white;
    }
    .active-badge {
        background-color: #4CAF50;
    }
    .inactive-badge {
        background-color: #777;
    }
    
    
    /* Ajustes responsivos */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.9em;
        }
        
        .btn {
            padding: 4px 8px;
            font-size: 12px;
        }
        
        h2 {
            font-size: 2em;
        }
    }
    
    /* Estilos específicos para los botones de exportación */
    .dt-buttons {
        margin-bottom: 10px;
    }
    
    .dt-button {
        margin-right: 5px;
    }
</style>
         
<div style="text-align: center; padding: 20px">
    <h2 class="h2-style">Lista de los Materiales</h2>

<div style="padding: 20px;">
    <div style="margin-bottom: 20px;">
        <a href="add_material.php" class="btn btn-primary">Agregar Nuevo Material</a>
    </div>

    <div class="table-responsive">
        <table id="table" class="table table-striped table-bordered" style="width:100%;">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Toneladas</th>
                    <th>Cantidad total</th>
                    <th>Unidad</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th class="actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($materiales) > 0): ?>
                    <?php foreach ($materiales as $material): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['name']); ?></td>
                            <td><?php echo htmlspecialchars($material['tonelada']); ?></td>
                            <td><?php echo htmlspecialchars($material['amount_total']); ?></td>
                            <td><?php echo htmlspecialchars($material['siglas']); ?></td>
                            <td><?php echo htmlspecialchars($material['details']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $material['estatus'] ? 'active-badge' : 'inactive-badge'; ?>">
                                    <?php echo $material['estatus'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td class="actions" style="text-align: center;">
                                <a href="edit_material2.php?edit_id=<?php echo $material['material_id']; ?>" class="btn btn-success btn-sm">Editar</a>
                                <a href="javascript:void(0);" 
                                   class="btn btn-danger btn-sm delete-btn" 
                                   data-id="<?php echo $material['material_id']; ?>"
                                   data-name="<?php echo htmlspecialchars($material['name']); ?>">Eliminar</a>
                                <a href="javascript:void(0);" 
                                   class="btn btn-warning btn-sm toggle-status-btn <?php echo $material['estatus'] ? 'btn-inactive' : 'btn-active'; ?>"
                                   data-id="<?php echo $material['material_id']; ?>"
                                   data-status="<?php echo $material['estatus']; ?>"
                                   data-name="<?php echo htmlspecialchars($material['name']); ?>">
                                    <?php echo $material['estatus'] ? 'Desactivar' : 'Activar'; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No se encontraron materiales</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

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
            "order": [[ 0, "asc" ]],
            "responsive": true,
            "dom": '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
                   '<"row"<"col-sm-12"tr>>' +
                   '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "buttons": [
                {
                    extend: 'copy',
                    text: 'Copiar',
                    className: 'btn btn-secondary btn-sm',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'csv',
                    text: 'CSV',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'excel',
                    text: 'Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'pdf',
                    text: 'PDF',
                    className: 'btn btn-danger btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    customize: function(doc) {
                        doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                    },
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'print',
                    text: 'Imprimir',
                    className: 'btn btn-primary btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                }
            ]
        });
    });

    // Mostrar mensaje de SweetAlert2 si existe
    <?php if (!empty($message)): ?>
        Swal.fire({
            title: '<?php echo $message_type === "success" ? "Éxito" : "Error"; ?>',
            text: '<?php echo addslashes($message); ?>',
            icon: '<?php echo $message_type; ?>',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>

    // Confirmación para cambiar estado
    document.querySelectorAll('.toggle-status-btn').forEach(button => {
    button.addEventListener('click', function() {
        const materialId = this.getAttribute('data-id');
        const currentStatus = this.getAttribute('data-status') === '1';
        const materialName = this.getAttribute('data-name');
        const actionText = currentStatus ? 'desactivar' : 'activar';
        const actionTextCap = currentStatus ? 'Desactivar' : 'Activar';
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Quieres ${actionText} el material "${materialName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Sí, ${actionText}`,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Usar AJAX en lugar de redirección
                fetch(`list_material.php?toggle_status=${materialId}`)
                    .then(response => response.text())
                    .then(() => {
                        // Recargar la página o actualizar solo el elemento cambiado
                        location.reload();
                    })
                    .catch(error => {
                        Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
                    });
            }
        });
    });
});

document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const materialId = this.getAttribute('data-id');
        const materialName = this.getAttribute('data-name');
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Quieres eliminar el material "${materialName}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loader mientras se procesa
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Hacer la petición
                fetch(`list_material.php?delete_id=${materialId}`)
                .then(response => {
                    // Siempre recargar la página después de eliminar
                    location.reload();
                })
                .catch(error => {
                    Swal.fire(
                        'Error',
                        'Ocurrió un error al intentar eliminar: ' + error.message,
                        'error'
                    );
                });
            }
        });
    });
});
</script>
</div>


</body>
</html>