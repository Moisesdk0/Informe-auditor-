<?php
$archivo = 'file/Manual de usuario.pdf'; // Ruta del archivo

// Verifica si el archivo existe
if (file_exists($archivo)) {
    // Configura las cabeceras para la descarga del archivo
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($archivo) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($archivo));
    flush(); // Limpiar el buffer
    readfile($archivo); // Leer y enviar el archivo
    exit; // Terminar el script
} else {
    echo "El archivo no existe."; // Mensaje de error si el archivo no se encuentra
}