<?php
session_start();
include("conexion.php");
require 'vendor/autoload.php';

$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

if(!$email) {
    $_SESSION['error'] = "Email no válido";
    header("Location: recuperar.php");
    exit();
}

// Verificar si el email existe
$stmt = $conn->prepare("SELECT user_id FROM user WHERE gmail = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $token = bin2hex(random_bytes(32));
    date_default_timezone_set('America/Caracas'); //Aca esta para configurar y que seleccione la zona horaria de venezuela
    $expiracion = time() + 3600; // 1 hora en timestamp UNIX
    $expiracion_mysql = date("Y-m-d H:i:s", $expiracion); // Para MySQL
    
    // Guardar token (corregido user_id)
    $stmt = $conn->prepare("UPDATE user SET token_recuperacion = ?, token_expiracion = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $token, $expiracion_mysql, $user['user_id']); // ← Cambiado a user_id
    $stmt->execute();
    
    // Configurar PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'uptag2026@gmail.com';
    $mail->Password = 'xwnlhyllvdpdamyn';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->SMTPDebug = 3; // ← Para depuración
    
    $mail->setFrom('no-reply@tudominio.com', 'Sistema de Recuperación');
    $mail->addAddress($email);
    $mail->isHTML(true);
    
    $mail->Subject = 'Recuperación de Contraseña';
    $enlace = "http://confalvi.infinityfreeapp.com/cambiar_password.php?token=$token&email=".urlencode($email); // ← URL corregida
    $mail->Body = "Haz clic para recuperar tu contraseña: <a href='$enlace'>$enlace</a> (válido por 1 hora)";
    
    if($mail->send()) {
        $_SESSION['success'] = "Se ha enviado un enlace a tu email";
    } else {
        $_SESSION['error'] = "Error al enviar el email: " . $mail->ErrorInfo;
    }
} else {
    $_SESSION['error'] = "El email no está registrado";
}

header("Location: recuperar.php");
exit();
?>