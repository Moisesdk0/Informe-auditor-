<?php
include("head2.php");
?>


<?php


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

    
<div style="text-align: center; padding: 30px 20px;">
 <h1 class="h1-style">Salida de los Materiales</h2>

<div class="table-responsive p-6">
    <?php if (!empty($message)): ?>
        <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?> p-4 mb-4 text-center rounded <?php echo strpos($message, 'Error') === false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <table id="table" class="table table-striped table-hover table-bordered w-full">
        <thead>
            <tr>
                <th>Código</th>
                <th>Material</th>
                <th>Cantidad</th>
                <th>Cantidad actual</th>
                <th>Toneladas</th>
                <th>Unidad</th>
                <th>Categoría</th>
                <th>Fecha de ingreso</th>
                <th>Fecha de expiración</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($materials) > 0): ?>
                <?php foreach ($materials as $material): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($material['code']); ?></td>
                        <td><?php echo htmlspecialchars($material['name']); ?></td>
                        <td><?php echo number_format($material['amount'], 2); ?></td>
                        <td><?php echo number_format($material['amount_total'], 2); ?></td>
                        <td><?php echo number_format($material['tonelada'], 3); ?></td>
                        <td><?php echo htmlspecialchars($material['nombre_unidad'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($material['nombre_categoria'] ?? 'N/A'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($material['date_income'])); ?></td>
                        <td><?php echo $material['date_expiration'] ? date('d/m/Y', strtotime($material['date_expiration'])) : 'N/A'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center text-gray-500">No se encontraron materiales</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    // Inicializar DataTable con configuración en español y botones
    $(document).ready(function () {
        $('#table').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" // Español
            },
            dom: 'Bfrtip', // Define dónde aparecen los botones
            buttons: [
                {
                    extend: 'copy',
                    text: 'Copiar',
                    className: 'btn btn-secondary'
                },
                {
                    extend: 'excel',
                    text: 'Excel',
                    className: 'btn btn-success text-white'
                },
                {
                    extend: 'pdf',
                    text: 'PDF',
                    className: 'btn btn-danger text-white'
                },
                {
                    extend: 'print',
                    text: 'Imprimir',
                    className: 'btn btn-info text-white'
                }
            ],
            pageLength: 10,
            lengthChange: true,
            ordering: true,
            searching: true,
            responsive: true
        });
    });
</script>
        <div class="mt-auto p-6 pt-0 text-center dark:text-white-dark ltr:sm:text-left rtl:sm:text-right">
            © <span id="footer-year">2025</span>. CONFALVI C.A.
        </div>
        <!-- end footer section -->
    </div>
</div>
    </div>
</body>
</html>