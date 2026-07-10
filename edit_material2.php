<?php
include("head.php");

// Verificar si se recibió un ID válido
if (!isset($_GET['edit_id']) || !is_numeric($_GET['edit_id'])) {
    header("Location: list_material.php");
    exit;
}

$material_id = intval($_GET['edit_id']);

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
    if (empty($nombre) || $unidad_id == 0 || $categoria_id == 0) {
        $message = "Por favor complete todos los campos requeridos";
        $message_class = "error";
    } else {
        // Actualizar en la base de datos
        $stmt = $conn->prepare("UPDATE material SET 
                              name = ?, 
                              tonelada = ?, 
                              amount_total = ?, 
                              unidad = ?, 
                              categoria = ?
                              WHERE material_id = ?");
        $stmt->bind_param("sddiii", $nombre, $tonelada, $cantidad_total, $unidad_id, $categoria_id, $material_id);

        if ($stmt->execute()) {
            // Registrar en bitácora
            $time = date('Y-m-d H:i:s');
            $action = 3; // Acción para modificación de material
            $insert_bitacora = $conn->prepare("INSERT INTO binnacle (time, action, user) VALUES (?, ?, ?)");
            $insert_bitacora->bind_param("sii", $time, $action, $id);
            $insert_bitacora->execute();

            $message = "Material actualizado correctamente";
            $message_class = "success";
        } else {
            $message = "Error al actualizar el material: " . $conn->error;
            $message_class = "error";
        }
    }
}

// Obtener datos actuales del material
$query_material = "SELECT m.*, c.details as categoria_nombre, u.siglas as unidad_siglas 
                   FROM material m
                   INNER JOIN categoria c ON m.categoria = c.categoria_id
                   INNER JOIN unidad u ON m.unidad = u.unidad_id
                   WHERE m.material_id = ?";
$stmt_material = $conn->prepare($query_material);
$stmt_material->bind_param("i", $material_id);
$stmt_material->execute();
$result_material = $stmt_material->get_result();

if ($result_material->num_rows == 0) {
    header("Location: list_material.php");
    exit;
}

$material = $result_material->fetch_assoc();

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


<link rel="stylesheet" type="text/css" href="CSS/h1-style.css">

    <h2 class="h2-style">Editar Materiales</h2>
    <div style="text-align: center; padding-top: 30px;">
        <h2 style="font-size: 3em; margin: 0;"></h2>
    </div>

    <div class="container">
        <?php if (isset($message)): ?>
            <div style="padding: 10px; background: #dff0d8; margin-bottom: 15px; display: flex; justify-content: center;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form action="edit_material2.php?edit_id=<?php echo $material_id; ?>" method="post">
            <div class="form-group">
                <label for="nombre" class="required">Nombre del Material</label>
                <input type="text" id="nombre" name="nombre" class="form-input" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : htmlspecialchars($material['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tonelada">Tonelada (kg)</label>
                <input type="number" id="tonelada" name="tonelada" class="form-input" step="0.000000001" min="0" value="<?php echo isset($_POST['tonelada']) ? htmlspecialchars($_POST['tonelada']) : htmlspecialchars($material['tonelada']); ?>">
            </div>
            
            <div class="form-group">
                <label for="cantidad_total">Cantidad Total</label>
                <input type="number" id="cantidad_total" name="cantidad_total" class="form-input" step="0.01" min="0" value="<?php echo isset($_POST['cantidad_total']) ? htmlspecialchars($_POST['cantidad_total']) : htmlspecialchars($material['amount_total']); ?>">
            </div>
            
            <div class="form-group">
                <label for="unidad_id" class="required">Unidad de Medida</label>
                <select id="unidad_id" name="unidad_id" class="form-input" required>
                    <option value="">Seleccione una unidad...</option>
                    <?php foreach ($unidades as $unidad): ?>
                        <option value="<?= $unidad['unidad_id'] ?>" 
                            <?= (isset($_POST['unidad_id']) && $_POST['unidad_id'] == $unidad['unidad_id']) || 
                                (!isset($_POST['unidad_id']) && $material['unidad'] == $unidad['unidad_id']) ? 'selected' : '' ?>>
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
                        <option value="<?= $categoria['categoria_id'] ?>" 
                            <?= (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $categoria['categoria_id']) || 
                                (!isset($_POST['categoria_id']) && $material['categoria'] == $categoria['categoria_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoria['details']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" align="center">
                
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>

        <div class="btn-volver-container">
            <a href="list_material.php" class="btn btn-dark btn-sm">Cancelar</a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        disableDefaultOption('unidad_id');
        disableDefaultOption('categoria_id');
    });
</script>
</body>
</html>