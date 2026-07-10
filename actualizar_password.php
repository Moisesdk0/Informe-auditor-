<?php
session_start();
include("conexion.php");

// 1. Verificar método POST y datos requeridos
if($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_POST['token']) || empty($_POST['email']) || empty($_POST['nueva_password']) || empty($_POST['confirmar_password'])) {
    $_SESSION['error'] = "Datos incompletos";
    header("Location: recuperar.php");
    exit();
}

$token = $_POST['token'];
$email = $_POST['email'];
$nueva_password = $_POST['nueva_password'];
$confirmar_password = $_POST['confirmar_password'];
$answer = strtolower(trim($_POST['answer']));

// 2. Validar coincidencia de contraseñas
if($nueva_password !== $confirmar_password) {
    $_SESSION['error'] = "Las contraseñas no coinciden";
    header("Location: cambiar_password.php?token=$token&email=".urlencode($email));
    exit();
}

// 3. Verificación en dos pasos (sesión + BD)
if(!isset($_SESSION['token_verification']) ||
$_SESSION['token_verification']['token'] != $token ||
$_SESSION['token_verification']['email'] != $email) {

 $_SESSION['error'] = "Sesión de recuperación inválida";
 header("Location: recuperar.php");
 exit();
}

// 4. Verificación final en base de datos
$stmt = $conn->prepare("SELECT user_id, security_response FROM user WHERE gmail = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$check_answer = strtolower(trim($row['security_response']));

if($check_answer == $answer){

    if($result->num_rows > 0) {
        // 5. Hashear la nueva contraseña
        $hashed_password = password_hash($nueva_password, PASSWORD_DEFAULT);
        $attempts = 1;

        // 6. Actualizar la contraseña hasheada, invalidar token
        $update_stmt = $conn->prepare("UPDATE user SET password = ?, attempts = ?, token_recuperacion = NULL, token_expiracion = NULL WHERE gmail = ?");
        $update_stmt->bind_param("sis", $hashed_password, $attempts, $email); // Ahora guardamos la contraseña hasheada

        if($update_stmt->execute()) {
            unset($_SESSION['token_verification']);

            // Mensaje de éxito
            $_SESSION['password_change_message'] = "Contraseña actualizada correctamente";

            // Redirigir con parámetro específico
            header("Location: index.php?source=password_update");
            exit();
        } else {
            unset($_SESSION['token_verification']);
            $_SESSION['password_change_message'] = "Error al actualizar la contraseña";
            header("Location: recuperar.php?error=password_update");
            exit();
        }
    } else {
        $_SESSION['error'] = "Token inválido, ya fue utilizado o expiró";
        header("Location: recuperar.php");
        exit();
    }
}else{
    $_SESSION['error'] = "La pregunta de seguridad es erronea";
    header("Location: recuperar.php");
    exit();
}
?>