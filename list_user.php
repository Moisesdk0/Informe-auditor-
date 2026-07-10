<?php
include("head.php");

// Procesar eliminación de usuario
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $stmt = $conn->prepare("SELECT rol FROM user WHERE user_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if($row['rol'] == 1){
            // No se puede eliminar otro administrador
            $_SESSION['message'] = "No se puede eliminar otro administrador";
            $_SESSION['message_type'] = "error";
        } else{
            $delete_stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
            $delete_stmt->bind_param("i", $delete_id);
            
            if ($delete_stmt->execute()) {
                // Registrar en bitácora
                $time = date('Y-m-d H:i:s');
                $action = 2; // Acción para eliminar usuario
                $insert_stmt = $conn->prepare("INSERT INTO binnacle (time, action, user) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("sii", $time, $action, $id);
                $insert_stmt->execute();
                
                $_SESSION['message'] = "Usuario eliminado correctamente";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error al eliminar el usuario: " . $conn->error;
                $_SESSION['message_type'] = "error";
            }
        }    
    } else{
        $_SESSION['message'] = "Usuario no encontrado";
        $_SESSION['message_type'] = "error";
    }
}

// Procesar cambio de estado activo/inactivo
if (isset($_GET['toggle_status'])) {
    
    $user_id = intval($_GET['toggle_status']);
        
    if($id != $user_id){
        // Obtener estado actual
        $result = mysqli_query($conn, "SELECT active FROM user WHERE user_id = $user_id");
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $new_status = $row['active'] ? 0 : 1; // Cambiar el estado
            
            // Actualizar estado
            $update = mysqli_query($conn, "UPDATE user SET active = $new_status, attempts = 0 WHERE user_id = $user_id");
            
            if ($update) {
                // Registrar en bitácora
                $time = date('Y-m-d H:i:s');
                $action = $new_status ? 4 : 5; // 4=Activación, 5=Desactivación
                $id ?? 0;
                mysqli_query($conn, "INSERT INTO binnacle (time, action, user) VALUES ('$time', $action, $id)");
                
                $_SESSION['message'] = "Estado del usuario actualizado correctamente";
                $_SESSION['message_type'] = "success";
            } else {    
                $_SESSION['message'] = "Error al actualizar el estado: " . mysqli_error($conn);
                $_SESSION['message_type'] = "error";
            }
        }
    } else{
        $_SESSION['message'] = "No se puede desactivar el usuario actual";
        $_SESSION['message_type'] = "error";
    }
    // Redirigir para evitar reenvío del formulario
    header("Location: list_user.php");
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

// Obtener lista de todos los usuarios
$users = [];
$query = "SELECT user_id, name, gmail, rol, active FROM user";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}
?>

<link rel="stylesheet" type="text/css" href="CSS/h1-style.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<!-- DataTables Bootstrap 5 CSS (para mejor estilo) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- jQuery (necesario para DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div style="text-align: center; padding: 30px 20px;">
    <h2 class="h2-style">Lista de Usuarios</h2>
    <div style="margin-top: 20px;">
        <a href="add_user.php" class="btn btn-primary">
            Agregar Nuevo Usuario
        </a>
    </div>
    <div class="table-responsive">
        <table id="table" class="table table-striped table-bordered" style="width:100%;">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['gmail']); ?></td>
                            <td><?php echo $user['rol'] == 1 ? 'Administrador' : 'Usuario'; ?></td>
                            <td>
                                <span class="status-badge <?php echo $user['active'] ? 'active-badge' : 'inactive-badge'; ?>">
                                    <?php echo $user['active'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td class="actions" style="text-align: center;">
                            <?php if ($user['rol'] == 2): ?>
                                <a href="edit_user.php?edit_id=<?php echo $user['user_id']; ?>" class="btn btn-success btn-sm">Editar</a>
                                <a href="javascript:void(0);" 
                                   class="btn btn-danger btn-sm delete-btn" 
                                   data-id="<?php echo $user['user_id']; ?>"
                                   data-name="<?php echo htmlspecialchars($user['name']); ?>">Eliminar</a>
                                <a href="javascript:void(0);" 
                                   class="btn btn-warning btn-sm toggle-status-btn <?php echo $user['active'] ? 'btn-inactive' : 'btn-active'; ?>"
                                   data-id="<?php echo $user['user_id']; ?>"
                                   data-status="<?php echo $user['active']; ?>"
                                   data-name="<?php echo htmlspecialchars($user['name']); ?>">
                                    <?php echo $user['active'] ? 'Desactivar' : 'Activar'; ?>
                                </a>
                            <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No se encontraron usuarios</td>
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
            "responsive": true
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

// Confirmación para eliminar usuario 
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        const userId = this.getAttribute('data-id');
        const userName = this.getAttribute('data-name');
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Quieres eliminar el usuario "${userName}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Usar AJAX en lugar de redirección
                fetch(`list_user.php?delete_id=${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('La respuesta de la red no fue exitosa');
                        }
                        return response.text();
                    })
                    .then(() => {
                        // Recargar la página o actualizar solo el elemento eliminado
                        location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'No se pudo eliminar el usuario', 'error');
                    });
            }
        });
    });
});

// Confirmación para cambiar estado de usuario 
document.querySelectorAll('.toggle-status-btn').forEach(button => {
    button.addEventListener('click', function() {
        const userId = this.getAttribute('data-id');
        const currentStatus = this.getAttribute('data-status') === '1';
        const userName = this.getAttribute('data-name');
        const actionText = currentStatus ? 'desactivar' : 'activar';
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Quieres ${actionText} el usuario "${userName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Sí, ${actionText}`,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Usar AJAX en lugar de redirección
                fetch(`list_user.php?toggle_status=${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('La respuesta de la red no fue exitosa');
                        }
                        return response.text();
                    })
                    .then(() => {
                        // Recargar la página o actualizar solo el elemento cambiado
                        location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'No se pudo cambiar el estado del usuario', 'error');
                    });
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
    
    .btn-warning {
        background-color: #ffc107;
        color: #000;
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
    
    .btn-edit {
        background-color: #4CAF50;
        color: white;
    }
    
    .btn-status {
        color: white;
    }
    
    .btn-active {
        background-color: rgb(43, 82, 255);
        border: rgb(43, 82, 255);
        box-shadow: 4 6px 5px -5px blue; 
        color: white;
    }
    
    .btn-inactive {
        background-color: #ff9800;
        box-shadow: 4 6px 5px -5px orange; 
        color: white;
    }
    
    .btn-delete {
        background-color: #f44336;
        color: white;
    }
    
    .btn-add {
        background-color: #2196F3;
        color: white;
        padding: 8px 15px;
        display: inline-block;
        margin-bottom: 20px;
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
</style>
</div>
</body>
</html>