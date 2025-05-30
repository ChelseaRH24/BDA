<?php
session_start();
session_destroy(); // Destruye toda la sesión
header("Location: ../index.php"); // Redirige al login
exit();
?>