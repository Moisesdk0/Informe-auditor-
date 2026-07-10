<?php
include("head.php");

// Incluir SweetAlert2 CSS y JS
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">';
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

// Inicializamos la variable de mensaje
$message = '';
$message_type = ''; // Para determinar si es éxito o error

// Verificamos si se envió el formulario de edición
if (isset($_POST['submit_edit'])) {

    $user_data = [];
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        $consul_stmt = $conn->prepare("SELECT user_id, name, gmail, security_question, security_response, rol FROM user WHERE user_id = ?");
        $consul_stmt->bind_param("i", $edit_id);
        $consul_stmt->execute();
        $consul_result = $consul_stmt->get_result();

        if ($consul_result->num_rows > 0) {
            $user_data = $consul_result->fetch_assoc();
        } else {
            $_SESSION['message'] = "Usuario no encontrado";
            $_SESSION['message_type'] = "error";
            header("Location: list_user.php");
            exit();
        }
        $consul_stmt->close();
    }
    

    // Obtenemos los datos del formulario
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $user = isset($_POST['user']) ? htmlspecialchars($_POST['user']) : '';
    $pregunta = isset($_POST['pregunta']) ? htmlspecialchars($_POST['pregunta']) : '';
    $respuesta = isset($_POST['respuesta']) ? htmlspecialchars($_POST['respuesta']) : '';
    $rol = isset($_POST['rol']) ? intval($_POST['rol']) : 2;

    // Verificamos si el usuario existe
    $check_user_stmt = $conn->prepare("SELECT user_id FROM user WHERE user_id = ?");
    $check_user_stmt->bind_param("i", $user_id);
    $check_user_stmt->execute();
    $check_user_result = $check_user_stmt->get_result();

    if ($check_user_result->num_rows > 0) {
        // Actualizamos el usuario, excluyendo la contraseña
        $update_stmt = $conn->prepare("UPDATE user SET name = ?, gmail = ?, security_question = ?, security_response = ?, attempts = 0 WHERE user_id = ?");
        $update_stmt->bind_param("ssssi", $user, $email, $pregunta, $respuesta, $user_id);

        if ($update_stmt->execute()) {
            // Registramos en la bitácora
            $time = date('Y-m-d H:i:s');
            $action = 3; // Acción para edición de usuario

            $insert_stmt2 = $conn->prepare("INSERT INTO binnacle (time, action, user) VALUES (?, ?, ?)");
            $insert_stmt2->bind_param("sii", $time, $action, $id);
            $insert_stmt2->execute();

            $_SESSION['message'] = "Usuario actualizado correctamente";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error al actualizar el usuario: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "El usuario no existe";
        $_SESSION['message_type'] = "error";
    }
    $check_user_stmt->close();
    $update_stmt->close();
    $insert_stmt2->close();
    
    // Si hubo un error, redirigir de vuelta al formulario
    if (!empty($_SESSION['message'])) {
        // La redirección se manejará con JavaScript más abajo
    }
}

// Obtener mensaje de la sesión si existe
$session_message = '';
$session_message_type = '';
if (isset($_SESSION['message'])) {
    $session_message = $_SESSION['message'];
    $session_message_type = $_SESSION['message_type'];
    // Limpiar los mensajes de la sesión
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// Obtener datos del usuario a editar
$user_data = [];
$edit_id = 0; // Inicializar $edit_id
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $consul_stmt = $conn->prepare("SELECT user_id, name, gmail, security_question, security_response, rol FROM user WHERE user_id = ?");
    $consul_stmt->bind_param("i", $edit_id);
    $consul_stmt->execute();
    $consul_result = $consul_stmt->get_result();

    if ($consul_result->num_rows > 0) {
        $user_data = $consul_result->fetch_assoc();
    } else {
        $_SESSION['message'] = "Usuario no encontrado";
        $_SESSION['message_type'] = "error";
        header("Location: list_user.php");
        exit();
    }
    $consul_stmt->close();
}

if (isset($user_data['rol']) && $user_data['rol'] === 1) { // Verificar que $user_data['rol'] exista
    $roles = "Administrador";
} else {
    $roles = "Usuario";
}

?>


<div style="text-align: center; padding-top: 30px;">
    <h2 style="font-size: 3em; margin: 0;">Editar Usuario</h2>

<div style="text-align: center; padding-top: 30px;">

<div class="container">
    <form method="POST" action="edit_user.php?edit_id=<?php echo $edit_id; ?>">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_data['user_id'] ?? ''); ?>">
        
        <div class="form-group">
            <label for="user">Nombre de usuario:</label>
            <input type="text" id="user" name="user" class="form-input" value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user_data['gmail'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="pregunta">Pregunta de seguridad:</label>
            <input type="text" id="pregunta" name="pregunta" class="form-input" value="<?php echo htmlspecialchars($user_data['security_question'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="respuesta">Respuesta de seguridad:</label>
            <input type="text" id="respuesta" name="respuesta" class="form-input" value="<?php echo htmlspecialchars($user_data['security_response'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="rol">Rol del usuario:</label>
            <input type="text" id="rol_display" name="rol_display" class="form-input" value="<?php echo htmlspecialchars($roles); ?>" readonly required>
            <!-- Campo oculto para mantener el valor del rol si es necesario en el POST -->
            <input type="hidden" name="rol" value="<?php echo htmlspecialchars($user_data['rol'] ?? '2'); ?>">
        </div>

        <div class="btn-volver-container">
            <button type="submit" name="submit_edit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>

    <div class="btn-volver-container">
        <a href="list_user.php" class="btn btn-dark btn-sm">Cancelar</a>
    </div>
</div>

<script>
    // Mostrar mensaje de SweetAlert2 si existe
    <?php if (!empty($session_message)): ?>
        Swal.fire({
            title: '<?php echo $session_message_type === "success" ? "Éxito" : "Error"; ?>',
            text: '<?php echo addslashes($session_message); ?>',
            icon: '<?php echo $session_message_type; ?>',
            confirmButtonText: 'OK'
        }).then((result) => {
            <?php if ($session_message_type === "success"): ?>
            // Si ya redirigimos en PHP para éxito, esto no se ejecuta
            // Pero si queremos redirigir aquí en caso de error:
            <?php else: ?>
            // Permanecer en la página para mostrar el error
            <?php endif; ?>
        });
    <?php endif; ?>
</script>
</body>
</html>