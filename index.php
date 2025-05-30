<?php 
session_start();
// Incluir conexión
include "Administrador/Bd/Conexion.php";

// Manejo de errores
$error = '';

// Procesar formulario cuando se envíe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validación básica
    if (empty($username) || empty($password)) {
        $error = "Por favor ingrese su nombre de usuario y contraseña.";
    } else {
        try {
            // Consulta corregida que incluye contrasenaHash
            $stmt = $pdo->prepare("SELECT u.idUsuario, u.nombreUsuario, u.contrasenaHash, u.estado, u.idPerfil, p.nombrePerfil 
                                 FROM usuarios u
                                 JOIN perfiles p ON u.idPerfil = p.idPerfil
                                 WHERE u.nombreUsuario = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // Verificar usuario y contraseña
            if ($user && $user['contrasenaHash'] === $password) {
                // Verificar estado del usuario
                if ($user['estado'] !== 'Activo') {
                    $error = "Tu cuenta no está activa.";
                } else {
                    // Actualizar fecha de última conexión
                    $pdo->prepare("UPDATE usuarios SET fechaUltimaConexion = NOW() WHERE idUsuario = ?")
                        ->execute([$user['idUsuario']]);
                    
                    // Guardar datos en sesión con nombre consistente
                    $_SESSION['usuario'] = [
                        'id' => $user['idUsuario'],
                        'nombreUsuario' => $user['nombreUsuario'],
                        'nombrePerfil' => $user['nombrePerfil'],
                        'idPerfil' => $user['idPerfil'],
                        'estado' => $user['estado']
                    ];
                    
                    // Redirigir según perfil
                    if ($user['nombrePerfil'] === 'Gerente') {
                        header("Location: Gerente/index.php");
                    } elseif ($user['nombrePerfil'] === 'Supervisor') {
                        header("Location: dashboard_supervisor.php");
                    } else {
                        header("Location: dashboard_cajero.php");
                    }
                    exit;
                }
            } else {
                $error = "Nombre de usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El root es bueno</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"  rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"  rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #0d6efd, #007bff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-card {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }

        .gradient-custom-2 {
            background: linear-gradient(to right, #0d6efd, #007bff);
        }

        .form-floating > .form-control {
            height: calc(3.5rem + 2px);
            padding: 1rem 0.75rem;
        }

        .form-floating > label {
            top: 0.5rem;
            padding: 0 0.75rem;
            font-size: 0.85rem;
        }

        .btn-login {
            background: linear-gradient(to right, #0d6efd, #007bff);
            color: white;
            padding: 1rem;
            font-weight: bold;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: scale(1.05);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 123, 255, 0.3);
        }

        .logo {
            width: 120px;
            margin-bottom: 1rem;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .text-title {
            text-transform: none !important;
        }

        .text-muted {
            text-transform: none !important;
        }
    </style>
</head>
<body>
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-xl-5">
                <div class="card login-card">
                    <div class="row g-0">
                        <div class="col-md-12">
                            <div class="card-body p-5">
                                <div class="text-center mb-4">
                                    <img src="prueba.png" class="logo" alt="Logo">
                                    <h4 class="mt-2 mb-0 text-title">Bienvenido a El root es bueno</h4>
                                    <p class="text-muted small">Sistema de gestión</p>
                                </div>

                                <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    <?= htmlspecialchars($error) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <?php endif; ?>

                                <form method="POST" class="needs-validation" novalidate>
                                    <p class="text-center mb-4">Por favor ingrese sus credenciales</p>

                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="usuario" name="usuario"
                                               required placeholder="Nombre de usuario" 
                                               value="<?= htmlspecialchars($username ?? '') ?>">
                                        <label for="usuario">Nombre de usuario</label>
                                        <div class="invalid-feedback">Por favor ingrese su nombre de usuario.</div>
                                    </div>

                                    <div class="form-floating mb-4">
                                        <input type="password" class="form-control" id="password" name="password"
                                               required placeholder="Contraseña">
                                        <label for="password">Contraseña</label>
                                        <div class="invalid-feedback">Por favor ingrese su contraseña.</div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button class="btn btn-login" type="submit">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>
                                            Iniciar sesión
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script> 
    <!-- FontAwesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script> 

    <!-- Validación del formulario -->
    <script>
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>