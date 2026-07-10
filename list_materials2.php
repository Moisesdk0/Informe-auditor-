<?php
include("head.php");

// Procesar eliminación de material
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Iniciar transacción
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Primero obtener los datos del movimiento que vamos a eliminar
        $query = "SELECT material, type, amount FROM movement WHERE materials_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Movimiento no encontrado");
        }
        
        $movimiento = $result->fetch_assoc();
        $material_id = $movimiento['material'];
        $tipo = $movimiento['type'];
        $cantidad = $movimiento['amount'];
        
        // 2. Actualizar el stock del material (invertir el movimiento)
        if ($tipo === 'entrada') {
            // Si era una entrada, restamos la cantidad
            $update_query = "UPDATE material SET amount_total = amount_total - ? WHERE material_id = ?";
        } else {
            // Si era una salida, sumamos la cantidad
            $update_query = "UPDATE material SET amount_total = amount_total + ? WHERE material_id = ?";
        }
        
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("di", $cantidad, $material_id);
        $stmt_update->execute();
        
        // 3. Ahora eliminar el movimiento
        $delete_stmt = $conn->prepare("DELETE FROM movement WHERE materials_id = ?");
        $delete_stmt->bind_param("i", $delete_id);
        $delete_stmt->execute();
        
        // 4. Registrar en bitácora
        $time = date('Y-m-d H:i:s');
        $action = 10;
        $insert_stmt = $conn->prepare("INSERT INTO binnacle (time, action, user) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sii", $time, $action, $id);
        $insert_stmt->execute();
        
        // Confirmar transacción
        mysqli_commit($conn);
        
        // Usar sesión para pasar el mensaje
        $_SESSION['message'] = "Movimiento eliminado y stock actualizado correctamente";
        $_SESSION['message_type'] = "success";
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($conn);
        $_SESSION['message'] = "Error al eliminar el movimiento: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
    
    // Redirigir para evitar reenvío del formulario
    header("Location: list_materials_salida.php"); // Asegúrate de que este sea el nombre correcto del archivo
    exit();
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
$materials = [];
$query = "SELECT movement.*, material.*, unidad.siglas AS nombre_unidad, categoria.details AS nombre_categoria FROM movement INNER JOIN material ON movement.material = material.material_id LEFT JOIN unidad ON material.unidad = unidad.unidad_id LEFT JOIN categoria ON material.categoria = categoria.categoria_id WHERE movement.type = 'salida'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $materials[] = $row;
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

<div style="text-align: center; padding: 30px 20px;">
    <h2 class="h2-style">Salida de los Materiales</h2>
    <div style="margin-top: 20px;">
        <a href="register_movement.php" class="btn btn-primary">
            Registrar movimiento
        </a>
    </div>


<div x-data="basic" style="padding: 20px;">
    <div class="table-responsive">
        <table id="table" class="table table-striped table-bordered" style="width:100%;">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Material</th>
                    <th>Cantidad</th>
                    <th>Cantidad actual</th>
                    <th>Toneladas</th>
                    <th>Unidad</th>
                    <th>Categoría</th>
                    <th>Fecha de Salida</th>
                    <th>Fecha de expiración</th>
                    <th class="actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($materials) > 0): ?>
                    <?php foreach ($materials as $material): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['code']); ?></td>
                            <td><?php echo htmlspecialchars($material['name']); ?></td>
                            <td><?php echo htmlspecialchars($material['amount']); ?></td>
                            <td><?php echo htmlspecialchars($material['amount_total']); ?></td>
                            <td><?php echo htmlspecialchars($material['tonelada']); ?></td>
                            <td><?php echo htmlspecialchars($material['nombre_unidad']); ?></td>
                            <td><?php echo htmlspecialchars($material['nombre_categoria']); ?></td>
                            <td><?php echo htmlspecialchars($material['date_income']); ?></td>
                            <td><?php echo htmlspecialchars($material['date_expiration']); ?></td>
                            <td class="actions" style="text-align: center;">
                                <a href="edit_material.php?edit_id=<?php echo $material['materials_id']; ?>" class="btn btn-success btn-sm">Editar</a>
                                <a href="javascript:void(0);" 
                                   class="btn btn-danger btn-sm delete-btn" 
                                   data-id="<?php echo $material['materials_id']; ?>"
                                   data-name="<?php echo htmlspecialchars($material['name']); ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center;">No se encontraron materiales</td>
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
                        columns: ':not(:last-child)'
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

    // Confirmación para eliminar
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const materialId = this.getAttribute('data-id');
            const materialName = this.getAttribute('data-name');
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Quieres eliminar el movimiento del material "${materialName}"? Esta acción no se puede deshacer y afectará el stock.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `list_materials_salida.php?delete_id=${materialId}`; // Asegúrate de que este sea el nombre correcto del archivo
                }
            });
        });
    });
</script>

<style>
    /* Estilos personalizados para los botones */
    .btn {
        padding: 6px 12px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
        margin: 2px;
        display: inline-block;
        text-align: center;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .btn-primary {
        background-color: #0d6efd;
        color: white;
    }
    
    .btn-success {
        background-color: #198754;
        color: white;
    }
    
    .btn-danger {
        background-color: #dc3545;
        color: white;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }
    
    .btn-info {
        background-color: #0dcaf0;
        color: white;
    }
    
    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
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
</div>
</body>
</html>
