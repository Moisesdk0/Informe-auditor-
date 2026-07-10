<?php
include("conexion.php");

$consul = mysqli_query($conn, "SELECT * FROM user WHERE name = 'Jesus'");
$num_filas = mysqli_num_rows($consul);
$result = mysqli_fetch_array($consul);

$intentos = $result['Attempts'];

function suma($intentos){
    return $intentos + 1;
} 

$suma = suma($intentos);

if($intentos < 3){
    echo "se equivoco de contrase;a usted tiene " . $intentos;
    $update_stmt = $conn->prepare("UPDATE user SET Attempts = ? WHERE user_id = 2");
    $update_stmt->bind_param("i", $suma);
} else{
    echo "Sobre paso el numero de intentos porfavor debe actualizar su contraseña";
}


?>