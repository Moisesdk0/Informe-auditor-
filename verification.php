<?php
session_start();
include("conexion.php");

$error = '';
$success = ''; // Variable para mensajes de éxito

if(isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if(isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recuperar Contraseña</title>
    <script>
        function showAlert(message, isError = false) {
            alert(message);
            if(isError) {
                // Opcional: puedes hacer algo adicional para errores
            }
        }
    </script>
</head>
<body>
    <h2>Recuperar Contraseña</h2>
    
    <form method="POST" action="enviar_token.php">
        <label>Email registrado:</label>
        <input type="email" name="email" required>
        <button type="submit">Enviar enlace</button>
    </form>

    <?php if($error || $success): ?>
        <script>
            <?php if($error): ?>
                showAlert("<?php echo addslashes($error); ?>", true);
            <?php endif; ?>
            
            <?php if($success): ?>
                showAlert("<?php echo addslashes($success); ?>");
            <?php endif; ?>
        </script>
    <?php endif; ?>
</body>
</html>