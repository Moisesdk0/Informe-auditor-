<?php
include("head2.php")
?>

<?php


// Obtener lista de todos los materiales
$materials = [];
$query = "SELECT movement.*, material.*, unidad.siglas AS nombre_unidad, categoria.details AS nombre_categoria FROM movement INNER JOIN material ON movement.material = material.material_id LEFT JOIN unidad ON material.unidad = unidad.unidad_id LEFT JOIN categoria ON material.categoria = categoria.categoria_id WHERE movement.type = 'entrada'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $materials[] = $row;
    }
}
?>



<link rel="stylesheet" type="text/css" href="CSS/h1-style.css">

    
<div style="text-align: center; padding: 30px 20px;">
 <h1 class="h1-style">Entrada de los Materiales</h2>

<div x-data="basic">
    <?php if (!empty($message)): ?>
        <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
<body x-data="main" class="relative overflow-x-hidden font-nunito text-sm font-normal antialiased" :class="[$store.app.sidebar ? 'toggle-sidebar' : '', $store.app.theme === 'dark' || $store.app.isDarkMode ? 'dark' : '', $store.app.menu, $store.app.layout,$store.app.rtlClass]">
    <!-- Aquí iría el resto del HTML del sidebar, header, etc., que ya tienes en tu archivo original -->
    <!-- ... (mantén tu estructura existente de sidebar y header) ... -->

    <div class="main-container min-h-screen text-black dark:text-white-dark" :class="[$store.app.navbar]">
        <!-- ... (tu contenido existente del header y sidebar) ... -->

        <div style="text-align: center; padding: 30px 20px;">
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
                                    <td><?php echo htmlspecialchars($material['amount']); ?></td>
                                    <td><?php echo htmlspecialchars($material['amount_total']); ?></td>
                                    <td><?php echo htmlspecialchars($material['tonelada']); ?></td>
                                    <td><?php echo htmlspecialchars($material['nombre_unidad']); ?></td>
                                    <td><?php echo htmlspecialchars($material['nombre_categoria']); ?></td>
                                    <td><?php echo htmlspecialchars($material['date_income']); ?></td>
                                    <td><?php echo htmlspecialchars($material['date_expiration']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center;">No se encontraron materiales</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
                    },
                    "buttons": {
                        "copy": "Copiar",
                        "colvis": "Visibilidad",
                        "collection": "Colección",
                        "colvisRestore": "Restaurar visibilidad",
                        "copyKeys": "Presione ctrl o u2318 + C para copiar los datos de la tabla al portapapeles del sistema. <br \/> <br \/> Para cancelar, haga clic en este mensaje o presione escape.",
                        "copySuccess": {
                            "1": "Copiada 1 fila al portapapeles",
                            "_": "Copiadas %ds filas al portapapeles"
                        },
                        "copyTitle": "Copiar al portapapeles",
                        "csv": "CSV",
                        "excel": "Excel",
                        "pageLength": {
                            "-1": "Mostrar todas las filas",
                            "_": "Mostrar %d filas"
                        },
                        "pdf": "PDF",
                        "print": "Imprimir",
                        "renameState": "Renombrar",
                        "updateState": "Actualizar",
                        "removeAllStates": "Borrar todos los estados",
                        "removeState": "Borrar",
                        "saveState": "Guardar",
                        "stateRestore": "Restaurar estado"
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
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'csv',
                        text: 'CSV',
                        className: 'btn btn-info btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: 'PDF',
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        customize: function(doc) {
                            doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                            doc.content.unshift({
                                text: 'Entrada de los materiales',
                                style: 'header',
                                alignment: 'center'
                            });
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Imprimir',
                        className: 'btn btn-primary btn-sm',
                        title: 'Entrada de los materiales'
                    }
                ]
            });
        });

    </script>
    <style>
        .btn {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
            margin: 2px;
        }
    </style>
        <div class="mt-auto p-6 pt-0 text-center dark:text-white-dark ltr:sm:text-left rtl:sm:text-right">            
            © <span id="footer-year">2025</span>. CONFALVI C.A.
        </div>
        <!-- end footer section -->
    </div>
</div>
</div>
</div>
</body>
</html>