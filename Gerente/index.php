<?php 

require_once '../Administrador/Bd/conexion.php';
require_once 'partials/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="h2">Dashboard</h1>
            <p class="text-muted">Panel de control</p>

            <!-- Tarjetas de estadísticas -->
            <div class="row g-3 mb-4">
                <?php
                // Contar empleados activos (personas con usuario asociado)
                $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'Activo'");
                $empleados_activos = $stmt->fetchColumn();
                
                // Contar usuarios por perfil
                $stmt = $pdo->query("SELECT p.nombrePerfil, COUNT(*) as cantidad 
                                   FROM usuarios u
                                   JOIN perfiles p ON u.idPerfil = p.idPerfil
                                   GROUP BY p.nombrePerfil");
                $usuarios_por_rol = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Contar usuarios inactivos
                $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'Inactivo'");
                $usuarios_inactivos = $stmt->fetchColumn();
                ?>
                
                <div class="col-md-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-person-check-fill display-4 mb-3"></i>
                            <h5 class="card-title"><?= $empleados_activos ?></h5>
                            <p class="card-text">Empleados Activos</p>
                        </div>
                    </div>
                </div>

                <?php foreach($usuarios_por_rol as $rol): ?>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-shield<?= $rol['nombrePerfil'] == 'Gerente' ? '-fill' : '' ?> display-4 mb-3"></i>
                            <h5 class="card-title"><?= $rol['cantidad'] ?></h5>
                            <p class="card-text"><?= $rol['nombrePerfil'] ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="col-md-3">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-person-x-fill display-4 mb-3"></i>
                            <h5 class="card-title"><?= $usuarios_inactivos ?></h5>
                            <p class="card-text">Usuarios Inactivos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos o información adicional -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Actividad Reciente</h5>
                            <span class="badge bg-primary">Últimas 24 horas</span>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Aquí puedes mostrar actividad reciente del sistema</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Acciones rápidas</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <a href="empleados.php?action=create" class="list-group-item list-group-item-action d-flex align-items-center">
                                    <i class="bi bi-person-plus me-2"></i> Nuevo empleado
                                </a>
                                <a href="usuarios.php?action=create" class="list-group-item list-group-item-action d-flex align-items-center">
                                    <i class="bi bi-person-add me-2"></i> Nuevo usuario
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>