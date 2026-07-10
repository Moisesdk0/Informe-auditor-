<?php
$servername = "localhost"; // Servidor de la base de datos
$username = "root";        // Usuario de MySQL
$password = "";            // Contraseña (vacía por defecto en XAMPP)
$database = "test2";       // Nombre de la base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

//echo "Conexión exitosa a 'test2'";

$conn = new mysqli("localhost", "root", "", "test2");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>