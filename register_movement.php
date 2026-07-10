<?php
include("head.php");

$message = '';

// Obtener lista de materiales activos
$materiales = [];
$query_materiales = "SELECT material_id, name FROM material WHERE estatus = 1 ORDER BY name";
$result_materiales = mysqli_query($conn, $query_materiales);

if ($result_materiales && mysqli_num_rows($result_materiales) > 0) {
    while ($row = mysqli_fetch_assoc($result_materiales)) {
        $materiales[] = $row;
    }
}

// Procesar formulario
if (isset($_POST['submit'])) {
    $material_id = intval($_POST['material_id']);
    $tipo = $_POST['tipo']; // 'entrada' o 'salida'
    $cantidad = floatval($_POST['cantidad']);
    $codigo = htmlspecialchars($_POST['codigo'] ?? '');
    $fecha_entrada = $_POST['fecha_entrada'] ?? date('Y-m-d H:i:s');
    $observaciones = htmlspecialchars($_POST['observaciones'] ?? '');
    if (!empty($_POST['fecha_expiracion'])) {
        $fecha_expiracion = $_POST['fecha_expiracion'];
    } else {
        $fecha_expiracion = NULL;
    }

    // Validaciones
    if ($material_id <= 0 || $cantidad <= 0 || !in_array($tipo, ['entrada', 'salida']) || empty($codigo)) {
        $message = "Error: Datos obligatorios faltantes o inválidos";
    } else {
        // Iniciar transacción
        mysqli_begin_transaction($conn);

        try {
            // 1. Registrar movimiento
            $query = "INSERT INTO movement (
                        material, 
                        type, 
                        amount, 
                        code,
                        date_income,
                        date_expiration
                    ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isssss", $material_id, $tipo, $cantidad, $codigo, $fecha_entrada, $fecha_expiracion);
            $stmt->execute();
            
            // 2. Actualizar stock del material
            if ($tipo === 'entrada') {
                $update_query = "UPDATE material SET amount_total = amount_total + ? WHERE material_id = ?";
            } else {
                // Verificar stock suficiente
                $check_stock = mysqli_query($conn, "SELECT amount_total FROM material WHERE material_id = $material_id");
                $stock = mysqli_fetch_assoc($check_stock)['amount_total'];
                
                if ($stock < $cantidad) {
                    throw new Exception("No hay suficiente stock para este material");
                }
                
                $update_query = "UPDATE material SET amount_total = amount_total - ? WHERE material_id = ?";
            }
            
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("di", $cantidad, $material_id);
            $stmt_update->execute();
            
            // 3. Registrar en bitácora
            
            $time = date('Y-m-d H:i:s');
            $action = ($tipo === 'entrada') ? 9 : 10; 
            $bitacora_query = "INSERT INTO binnacle (time, action, user) VALUES (?, ?, ?)";
            $stmt_bitacora = $conn->prepare($bitacora_query);
            $stmt_bitacora->bind_param("sii", $time, $action, $id);
            $stmt_bitacora->execute();
            
            
            mysqli_commit($conn);
            $message = "Movimiento registrado correctamente!";
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<div style="text-align: center; padding-top: 20px;">
  <h2 style="font-size: 2.5em; margin: 0;">Registrar movimiento</h2>
</div>

<div style="text-align: center; padding-top: 20px;">
</div>


    <div class="container" >
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="tipo">Tipo de movimiento:</label>
                <select name="tipo" id="tipo" class="form-input" required>
                    <option value="">Seleccione...</option>
                    <option value="entrada" <?= isset($_POST['tipo']) && $_POST['tipo'] == 'entrada' ? 'selected' : '' ?>>Entrada de material</option>
                    <option value="salida" <?= isset($_POST['tipo']) && $_POST['tipo'] == 'salida' ? 'selected' : '' ?>>Salida de material</option>
                </select>
            </div>
            
<div class="form-group">
    <label for="material_id">Material:</label>
    <select name="material_id" id="material_id" class="form-input" required>
        <option value="" <?= empty($_POST['material_id']) ? '' : 'disabled hidden' ?>>Seleccione un material...</option>
        <?php foreach ($materiales as $mat): ?>
            <option value="<?= $mat['material_id'] ?>" 
                class="material-option"
                data-codigo="<?= htmlspecialchars($mat['code'] ?? '') ?>"
                <?= isset($_POST['material_id']) && $_POST['material_id'] == $mat['material_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($mat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
            
            <div class="form-group">
                <label for="codigo">Código del movimiento:</label>
                <input type="text" name="codigo" id="codigo" class="form-input" required>
            </div>
            
            <div class="flex-row">
        <div class="form-group">
            <label for="cantidad">Cantidad:</label>
            <input type="number" name="cantidad" min="1" class="form-input" required 
                value="<?= isset($_POST['cantidad']) ? htmlspecialchars($_POST['cantidad']) : '' ?>"
                oninput="this.value = Math.abs(parseInt(this.value)) || 1">
        </div>
                
                <div class="form-group">
                    <label for="fecha_entrada">Fecha de entrada:</label>
                    <input type="datetime-local" name="fecha_entrada" class="form-input" required
                           value="<?= isset($_POST['fecha_entrada']) ? htmlspecialchars($_POST['fecha_entrada']) : date('Y-m-d\TH:i') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="fecha_expiracion">Fecha de expiración (opcional):</label>
                <input type="date" name="fecha_expiracion" class="form-input"
                       value="<?= isset($_POST['fecha_expiracion']) ? htmlspecialchars($_POST['fecha_expiracion']) : '' ?>">
            </div>

            <div align= "center">
            <button type="submit" name="submit" class="btn btn-primary">Registrar Movimiento</button>
            </div>
        </form>

        <div class="btn-volver-container">
    <a href="list_materials.php" class="btn btn-dark btn-sm">Volver</a>
    </div>
    </div>

<script>
document.getElementById('material_id').addEventListener('change', function() {
    const defaultOption = this.querySelector('option[value=""]');
    if (this.value !== "" && defaultOption) {
        defaultOption.disabled = true; // Deshabilita la opción por defecto
        defaultOption.hidden = true;    // Oculta la opción por defecto (opcional)
    }
});
document.getElementById('tipo').addEventListener('change', function() {
    const defaultOption = this.querySelector('option[value=""]');
    if (this.value !== "" && defaultOption) {
        defaultOption.disabled = true;
        defaultOption.hidden = true;
    }
});

</script>
</body>
</html>