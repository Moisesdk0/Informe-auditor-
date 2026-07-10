<?php
include("head.php");

// Inicializamos la variable de mensaje
$message = '';
$message_type = ''; // Para determinar si es éxito o error

// Verificamos si se envió el formulario
if (isset($_POST['submit'])) {
    // Obtenemos y sanitizamos los datos del formulario
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; // No htmlspecialchars para contraseña
    $user = isset($_POST['user']) ? htmlspecialchars($_POST['user']) : '';
    $pregunta = isset($_POST['pregunta']) ? htmlspecialchars($_POST['pregunta']) : '';
    $respuesta = isset($_POST['respuesta']) ? htmlspecialchars($_POST['respuesta']) : '';
    $rol = isset($_POST['rol']) ? htmlspecialchars($_POST['rol']) : '';

    // Validación de contraseña en el servidor
    if (strlen($password) < 16 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $message = "La contraseña debe tener al menos 16 caracteres, una mayúscula y un símbolo.";
        $message_type = "error";
    } else {
        // Hash de la contraseña antes de guardarla
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Verificar si el usuario ya existe
        $check_user = mysqli_query($conn, "SELECT * FROM user WHERE name = '$user' OR gmail = '$email'");
        if (mysqli_num_rows($check_user) > 0) {
            $message = "El nombre de usuario o correo electrónico ya está registrado";
            $message_type = "error";
        } else {
            // Insertar nuevo usuario
            $insert_stmt = $conn->prepare("INSERT INTO user (name, password, gmail, security_question, security_response, rol) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssssi", $user, $hashed_password, $email, $pregunta, $respuesta, $rol);

            if ($insert_stmt->execute()) {
                // Registrar en bitácora
                $time = date('Y-m-d H:i:s');
                $action = 1; // Acción de creación de usuario
                
                $insert_stmt2 = $conn->prepare("INSERT INTO binnacle (time, action, user) VALUES (?, ?, ?)");
                $insert_stmt2->bind_param("sii", $time, $action, $id);
                
                if ($insert_stmt2->execute()) {
                    $message = "Usuario creado exitosamente";
                    $message_type = "success";
                    // Limpiar campos después de éxito
                    $_POST = array();
                } else {
                    $message = "Error al registrar en bitácora";
                    $message_type = "error";
                }
            } else {
                $message = "Error al crear el usuario: " . $conn->error;
                $message_type = "error";
            }
        }
    }
}
?>

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div style="text-align: center; padding-top: 20px;">
  <h2 style="font-size: 2.5em; margin: 0;">Agregar usuario</h2>
</div>

<div style="text-align: center; padding-top: 20px;">
</div>

<div class="container" >
    <form  method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> " id="addUserForm">
        <div class="form-group">
            <label for="user">Nombre de usuario:</label>
            <input type="text" id="user" name="user" class="form-input" required
                value="<?= isset($_POST['user']) ? htmlspecialchars($_POST['user']) : '' ?>">
        </div>
        
<div class="form-group">
    <label for="password">Contraseña:</label>
    <div style="position: relative;">
        <input 
            type="password" 
            id="password" 
            name="password" 
            class="form-input" 
            required 
            minlength="8"
            pattern="^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\\|,.<>\/?]).{8,}$"
            oninput="validatePassword()"
            style="padding-right: 40px;"
        >
        <span 
            id="togglePassword" 
            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"
            onclick="togglePasswordVisibility()"
        >
            ver
        </span>
    </div>
    <div id="passwordError" style="color: red;"></div>
</div>

        <div class="form-group">
            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="email" class="form-input" required
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>

        <div class="form-group">
            <label for="pregunta">Pregunta de seguridad:</label>
            <input type="text" id="pregunta" name="pregunta" class="form-input" required
                value="<?= isset($_POST['pregunta']) ? htmlspecialchars($_POST['pregunta']) : '' ?>">
        </div>

        <div class="form-group">
            <label for="respuesta">Respuesta de seguridad:</label>
            <input type="text" id="respuesta" name="respuesta" class="form-input" required
                value="<?= isset($_POST['respuesta']) ? htmlspecialchars($_POST['respuesta']) : '' ?>">
        </div>

        <div class="form-group">
            <label for="rol">Rol del usuario:</label>
            <select name="rol" id="rol" class="form-input" required>
                <option value="">Seleccione...</option>
                <option value="1" <?= isset($_POST['rol']) && $_POST['rol'] == '1' ? 'selected' : '' ?>>Admin</option>
                <option value="2" <?= isset($_POST['rol']) && $_POST['rol'] == '2' ? 'selected' : '' ?>>Usuario</option>
            </select>
        </div>

        <div align="center">
        <button type="submit" name="submit" class="btn btn-primary" >Ingresar</button>
        </div>
    </form>

    <div class="btn-volver-container">
        <a href="list_user.php" class="btn btn-dark btn-sm">Volver</a>
    </div>
</div>

<script>
    // mostrar mensaje de SweetAlert2 
    <?php if(!empty($message)): ?>
        Swal.fire({
            title: '<?php echo $message_type === "success" ? "Éxito" : "Error"; ?>',
            text: '<?php echo addslashes($message); ?>',
            icon: '<?php echo $message_type; ?>',
            confirmButtonText: 'OK'
        }).then((result) => {
            <?php if($message_type === "success"): ?>
            if (result.isConfirmed) {
                window.location.href = "list_user.php";
            }
            <?php endif; ?>
        });
    <?php endif;?>

    document.addEventListener('DOMContentLoaded', function() {
        disableDefaultOption('rol');
    });
    
    // funciones para la contraseña 
    function validatePassword() {
        // lógica de validación de contraseña
        const password = document.getElementById('password').value;
        const errorDiv = document.getElementById('passwordError');
        
        if (password.length < 16) {
            errorDiv.textContent = 'La contraseña debe tener al menos 16 caracteres.';
        } else if (!/[A-Z]/.test(password)) {
            errorDiv.textContent = 'La contraseña debe incluir al menos una letra mayúscula.';
        } else if (!/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password)) {
            errorDiv.textContent = 'La contraseña debe incluir al menos un símbolo.';
        } else {
            errorDiv.textContent = '';
        }
    }
    
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleSpan = document.getElementById('togglePassword');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleSpan.textContent = 'ocultar';
        } else {
            passwordInput.type = 'password';
            toggleSpan.textContent = 'ver';
        }
    }
</script>
</body>
</html>