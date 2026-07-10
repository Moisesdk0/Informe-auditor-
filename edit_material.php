<?php
include("head.php");

// Incluir SweetAlert2 CSS y JS en el head
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">';
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

$message = '';
$message_type = ''; // Variable para el tipo de mensaje (success o error)
$movimiento = null;

// Obtener materiales activos
$materiales = mysqli_query($conn, "SELECT material_id, name FROM material WHERE estatus = 1");

// --- Process Editing ---
if(isset($_POST['submit_edit'])) {
    // Re-fetch movimiento data to get original amount for calculation
    if(isset($_POST['movement_id'])) {
        $edit_id_post = intval($_POST['movement_id']);
        $query_original_mov = "SELECT * FROM movement WHERE materials_id = ?";
        $stmt_original_mov = $conn->prepare($query_original_mov);
        $stmt_original_mov->bind_param("i", $edit_id_post);
        $stmt_original_mov->execute();
        $result_original_mov = $stmt_original_mov->get_result();
        if($result_original_mov && mysqli_num_rows($result_original_mov) > 0) {
            $movimiento = mysqli_fetch_assoc($result_original_mov);
        } else {
            $message = "Error: No se encontró el movimiento original para editar.";
            $message_type = "error";
            goto end_of_post_processing;
        }
    }
    $movement_id = intval($_POST['movement_id']);
    $material_id = intval($_POST['material_id']);
    $tipo = $_POST['tipo'];
    $cantidad = floatval($_POST['cantidad']);
    $codigo = mysqli_real_escape_string($conn, $_POST['codigo']);
    $fecha_entrada = $_POST['fecha_entrada'];
    $fecha_expiracion = !empty($_POST['fecha_expiracion']) ? $_POST['fecha_expiracion'] : null;
    
    // Get the current amount_total from the material
    $query_select = "SELECT amount_total FROM material WHERE material_id = ?";
    $stmt_select = $conn->prepare($query_select);
    $stmt_select->bind_param("i", $material_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $row = $result->fetch_assoc();
    if (!$row) {
        $message = "Error: No se encontró el material con ID $material_id.";
        $message_type = "error";
        goto end_of_post_processing;
    }
    $current_material_amount = $row['amount_total'];
    $actually = 0; 
    if ($tipo == "entrada") {
        $actually = ($current_material_amount - $movimiento['amount']) + $cantidad;
    } else { // $tipo == "salida"
        $actually = ($current_material_amount + $movimiento['amount']) - $cantidad;
    }
    if ($actually < 0) {
        $message = "Error (Server-side): El monto resultante no puede ser negativo. Por favor, revise los datos.";
        $message_type = "error";
        goto end_of_post_processing;
    }
    $query_update_material = "UPDATE material SET amount_total = ? WHERE material_id = ?";
    $stmt_update_material = $conn->prepare($query_update_material);
    $stmt_update_material->bind_param("di", $actually, $material_id);
    if (!$stmt_update_material->execute()) {
        $message = "Error al actualizar material: " . $conn->error;
        $message_type = "error";
        goto end_of_post_processing;
    }
    // --- Update the movement record ---
    $query_update_movement = "UPDATE movement SET 
                              material = ?, 
                              type = ?, 
                              amount = ?, 
                              code = ?,
                              date_income = ?,
                              date_expiration = ?
                              WHERE materials_id = ?";
    $stmt_update_movement = $conn->prepare($query_update_movement);
    $stmt_update_movement->bind_param("isdsssi", 
                                       $material_id, 
                                       $tipo, 
                                       $cantidad, 
                                       $codigo, 
                                       $fecha_entrada, 
                                       $fecha_expiracion,
                                       $movement_id);
    if($stmt_update_movement->execute()) {
        $message = "Movimiento actualizado correctamente.";
        $message_type = "success";
        // --- Binnacle logging ---
        $time = date('Y-m-d H:i:s');
        $action = 12;
        $insert_stmt2 = $conn->prepare("INSERT INTO binnacle (time, action, user) VALUES (?, ?, ?)");
        $insert_stmt2->bind_param("sii", $time, $action, $id);
        if (!$insert_stmt2->execute()) {
            error_log("Error al insertar en la bitácora: " . $conn->error);
        }
    } else {
        $message = "Error al actualizar el movimiento: " . $conn->error;
        $message_type = "error";
    }
    end_of_post_processing:;
}

if(isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $query_fetch_movement = "SELECT * FROM movement WHERE materials_id = ?";
    $stmt_fetch_movement = $conn->prepare($query_fetch_movement);
    $stmt_fetch_movement->bind_param("i", $edit_id);
    $stmt_fetch_movement->execute();
    $result_fetch_movement = $stmt_fetch_movement->get_result();
    if($result_fetch_movement && mysqli_num_rows($result_fetch_movement) > 0) {
        $movimiento = mysqli_fetch_assoc($result_fetch_movement);
    } else {
        $message = "Error: Movimiento no encontrado.";
        $message_type = "error";
    }
}

// Prepare values to be passed to JavaScript
$js_movimiento_amount = $movimiento['amount'] ?? 0;
$js_material_id = $movimiento['material'] ?? 0;
$js_current_total_amount = 0;

// If we have the material ID, fetch its current total amount for JS calculation
if ($js_material_id > 0) {
    $query_js_total = "SELECT amount_total FROM material WHERE material_id = ?";
    $stmt_js_total = $conn->prepare($query_js_total);
    $stmt_js_total->bind_param("i", $js_material_id);
    $stmt_js_total->execute();
    $result_js_total = $stmt_js_total->get_result();
    if ($row_js_total = $result_js_total->fetch_assoc()) {
        $js_current_total_amount = $row_js_total['amount_total'];
    }
}
?>
<link rel="stylesheet" type="text/css" href="CSS/h1-style.css">

    <h2 class="h2-style">Editar Movimiento</h2>
<br>
<div class="container">
    <form method="post" id="editMovementForm" onsubmit="return validateFormAndDates()">
        <input type="hidden" name="movement_id" value="<?= htmlspecialchars($movimiento['materials_id'] ?? '') ?>">
        
        <div class="form-group">
            <label>Tipo de movimiento:</label>
            <select name="tipo_disabled" id="tipoMovimiento" class="form-input" required disabled>
                <option value="entrada" <?= ($movimiento['type'] ?? '') == 'entrada' ? 'selected' : '' ?>>Entrada</option>
                <option value="salida" <?= ($movimiento['type'] ?? '') == 'salida' ? 'selected' : '' ?>>Salida</option>
            </select>
            <input type="hidden" name="tipo" value="<?= htmlspecialchars($movimiento['type'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label>Material:</label>
            <select name="material_id" id="materialId" class="form-input" required>
                <?php 
                mysqli_data_seek($materiales, 0);
                while($mat = mysqli_fetch_assoc($materiales)): ?>
                    <option value="<?= htmlspecialchars($mat['material_id']) ?>" 
                        <?= (isset($movimiento['material']) && $movimiento['material'] == $mat['material_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Código:</label>
            <input type="text" name="codigo" class="form-input" value="<?= htmlspecialchars($movimiento['code'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label>Cantidad:</label>
            <input type="number" step="0.01" name="cantidad" id="cantidad" class="form-input" oninput="this.value = Math.abs(parseFloat(this.value)) || 1" value="<?= htmlspecialchars($movimiento['amount'] ?? '') ?>" required min="0.01">
        </div>
        
        <div class="form-group">
            <label>Fecha de entrada:</label>
            <input type="date" name="fecha_entrada" class="form-input" 
                    value="<?= isset($movimiento['date_income']) ? htmlspecialchars(date('Y-m-d', strtotime($movimiento['date_income']))) : '' ?>" required>
        </div>
        
        <div class="form-group">
            <label>Fecha de expiración:</label>
            <input type="date" name="fecha_expiracion" class="form-input"
                    value="<?= isset($movimiento['date_expiration']) ? htmlspecialchars(date('Y-m-d', strtotime($movimiento['date_expiration']))) : '' ?>">
        </div>
        
        <div align="center">
            <button type="submit" name="submit_edit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
    
    <div class="btn-volver-container">
        <a href="list_materials.php" class="btn btn-dark btn-sm">Cancelar</a>
    </div>
</div>

<script>
    // PHP variables passed to JavaScript
    const originalMovementAmount = parseFloat(<?= json_encode($js_movimiento_amount) ?>);
    const initialMaterialTotalAmount = parseFloat(<?= json_encode($js_current_total_amount) ?>);
    
    // Mostrar mensaje de SweetAlert2 si existe
    <?php if (!empty($message)): ?>
        Swal.fire({
            title: '<?php echo $message_type === "success" ? "Éxito" : "Error"; ?>',
            text: '<?php echo addslashes($message); ?>',
            icon: '<?php echo $message_type; ?>',
            confirmButtonText: 'OK'
        }).then((result) => {
            <?php if ($message_type === "success"): ?>
            if (result.isConfirmed) {
                window.location.href = "list_materials.php";
            }
            <?php endif; ?>
        });
    <?php endif; ?>

    // Function for date validation
    function validarFechas() {
        const fechaEntrada = new Date(document.querySelector('input[name="fecha_entrada"]').value);
        const fechaExpiracion = document.querySelector('input[name="fecha_expiracion"]').value;
        if(fechaExpiracion) {
            const fechaExp = new Date(fechaExpiracion);
            if(fechaEntrada > fechaExp) {
                Swal.fire({
                    title: 'Error',
                    text: 'La fecha de expiración no puede ser anterior a la fecha de entrada',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }
        }
        return true;
    }

    // New function for quantity validation (client-side)
    function validarCantidad() {
        const tipo = document.getElementById('tipoMovimiento').value;
        const nuevaCantidad = parseFloat(document.getElementById('cantidad').value);
        if (isNaN(nuevaCantidad) || nuevaCantidad <= 0) {
            Swal.fire({
                title: 'Error',
                text: 'Por favor, ingrese una cantidad válida (mayor que cero).',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return false;
        }
        let calculatedActual = 0;
        if (tipo === "entrada") {
            calculatedActual = (initialMaterialTotalAmount - originalMovementAmount) + nuevaCantidad;
        } else {
            calculatedActual = (initialMaterialTotalAmount + originalMovementAmount) - nuevaCantidad;
        }
        if (calculatedActual < 0) {
            Swal.fire({
                title: 'Error',
                text: 'No hay suficientes materiales para la salida.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return false;
        }
        return true;
    }

    // Combined validation function for form submission
    function validateFormAndDates() {
        if (!validarFechas()) {
            return false;
        }
        if (!validarCantidad()) {
            return false;
        }
        return true;
    }
</script>
</body>
</html>