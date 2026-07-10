<?php
include("head.php");

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger y sanitizar los datos del formulario
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $tonelada = floatval($_POST['tonelada']);
    $cantidad_total = floatval($_POST['cantidad_total']);
    $unidad_id = intval($_POST['unidad_id']);
    $categoria_id = intval($_POST['categoria_id']);
    $estatus = isset($_POST['estatus']) ? 1 : 0;

    // Validar datos requeridos
    if (empty($nombre) ||  $unidad_id == 0 || $categoria_id == 0) {
        $error = "Por favor complete todos los campos requeridos";
    } else {
        // Insertar en la base de datos
        $stmt = $conn->prepare("INSERT INTO material (name, tonelada, amount_total, unidad, categoria) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sddii", $nombre, $tonelada, $cantidad_total, $unidad_id, $categoria_id);

        if ($stmt->execute()) {
            // Registrar en bitácora
            $time = date('Y-m-d H:i:s');
            $action = 13; // Acción para creación de material
            $insert_bitacora = $conn->prepare("INSERT INTO binnacle (time, action, user) VALUES (?, ?, ?)");
            $insert_bitacora->bind_param("sii", $time, $action, $id);
            $insert_bitacora->execute();

            $success = "Material registrado correctamente";
            // Limpiar campos después de registro exitoso
            $_POST = array();
        } else {
            $error = "Error al registrar el material: " . $conn->error;
        }
    }
}

// Obtener unidades y categorías para los select
$unidades = [];
$categorias = [];
$query_unidades = "SELECT * FROM unidad";
$query_categorias = "SELECT * FROM categoria";

$result_unidades = mysqli_query($conn, $query_unidades);
$result_categorias = mysqli_query($conn, $query_categorias);

if ($result_unidades && mysqli_num_rows($result_unidades) > 0) {
    while ($row = mysqli_fetch_assoc($result_unidades)) {
        $unidades[] = $row;
    }
}

if ($result_categorias && mysqli_num_rows($result_categorias) > 0) {
    while ($row = mysqli_fetch_assoc($result_categorias)) {
        $categorias[] = $row;
    }
}
?>


<div style="text-align: center; padding-top: 30px;">
    <h2 style="font-size: 3em; margin: 0;">Registrar nuevo material</h2>
</div>

    <div style="text-align: center; padding-top: 30px;">
        <h2 style="font-size: 3em; margin: 0;"></h2>
    </div>


    <div class="container">
        
        <?php if (isset($success)): ?>
            <div style="padding: 10px; background: #dff0d8; margin-bottom: 15px; display: flex; justify-content: center;" class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div style="padding: 10px; background: #dff0d8; margin-bottom: 15px; display: flex; justify-content: center;" class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="add_material.php" method="post">
            <div class="form-group">
                <label for="nombre"  class="required">Nombre del Material</label>
                <input type="text" id="nombre" name="nombre" class="form-input" placeholder="Coloque un nombre para el material" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" required>
            </div>
            
        <div class="form-group">
            <label for="tonelada">Peso en toneladas</label>
<input
    class="form-input"
    type="text" id="tonelada"
    name="tonelada"
    required
    value="<?php echo isset($_POST['tonelada']) ? htmlspecialchars($_POST['tonelada']) : '0'; ?>"
    oninput="
        let value = this.value;

        
        value = value.replace(/[^0-9.]/g, '');

        
        if (value === '.' || value.startsWith('.')) {
            value = '0' + value;
        }

        
        const parts = value.split('.');
        if (parts.length > 2) {
            // Si hay más de un punto, toma la parte antes del primero y el resto sin puntos adicionales
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        // 4. Asegurarse de que el valor no sea negativo (aunque ya lo evitamos, es una doble verificación).
        // También si el valor queda vacío (después de eliminar caracteres), se establece en '0'.
        if (value === '' || parseFloat(value) < 0) {
            this.value = '';
        } else {
            this.value = value;
        }
    "
    inputmode="decimal" >
        </div>
            
<div class="form-group">
    <label for="cantidad_total">Cantidad Total</label>
    <input 
        type="number" 
        id="cantidad_total" 
        name="cantidad_total" 
        class="form-input" 
        step="1"       
        min="0"       
        value="<?php echo isset($_POST['cantidad_total']) ? htmlspecialchars($_POST['cantidad_total']) : '0'; ?>"
        onkeypress="return event.charCode >= 48 && event.charCode <= 57" 
        oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value < 0) this.value = 0;"  
    >
</div>
            
            <div class="form-group">
                <label for="unidad_id" class="required">Unidad de Medida</label>
                <select id="unidad_id" name="unidad_id" class="form-input" required>
                    <option value="">Seleccione una unidad...</option>
                    <?php foreach ($unidades as $unidad): ?>
                        <option value="<?= $unidad['unidad_id'] ?>" <?= isset($_POST['unidad_id']) && $_POST['unidad_id'] == $unidad['unidad_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($unidad['description'] . ' (' . $unidad['siglas'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="categoria_id" class="required">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="form-input" required>
                    <option value="">Seleccione una categoría...</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['categoria_id'] ?>" <?= isset($_POST['categoria_id']) && $_POST['categoria_id'] == $categoria['categoria_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoria['details']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div align="center">
            <button type="submit" class="btn btn-primary">Registrar Material</button>
            </div>

                <div class="btn-volver-container">
        <a href="list_material.php" class= "btn btn-dark btn-sm">Cancelar</a>
    </div>
        </form>
    </div>

    <script>   
// Aplicar a todos los selects necesarios
disableDefaultOption('unidad_id');
disableDefaultOption('categoria_id');
disableDefaultOption('material_id'); // Si también existe
disableDefaultOption('tipo');        // Si también existe
</script>
</body>
</html>