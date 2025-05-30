<?php 

require_once '../Administrador/Bd/conexion.php';

// Manejo de sesión (validación centralizada)


$action = $_GET['action'] ?? 'list';

// Manejo de operaciones CRUD y AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'save') {
        $idUsuario = $_POST['idUsuario'] ?? null;
        $idPersona = $_POST['idPersona'];
        $nombreUsuario = $_POST['nombreUsuario'];
        $contrasena = $_POST['contrasena'];
        $idPerfil = $_POST['idPerfil'];
        $estado = $_POST['estado'];
        
        // Verificar si el empleado ya tiene un usuario
        if (!$idUsuario) {
            $check = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE idPersona = ?");
            $check->execute([$idPersona]);
            if ($check->fetchColumn() > 0) {
                header('Location: usuarios.php?error=empleado_usado');
                exit;
            }
        }

        if ($idUsuario) {
            $stmt = $pdo->prepare("UPDATE usuarios SET 
                idPersona = ?, nombreUsuario = ?, contrasenaHash = ?, idPerfil = ?, estado = ?
                WHERE idUsuario = ?");
            $stmt->execute([$idPersona, $nombreUsuario, $contrasena, $idPerfil, $estado, $idUsuario]);
            header('Location: usuarios.php?success=edit');
        } else {
            $stmt = $pdo->prepare("INSERT INTO usuarios 
                (idPersona, nombreUsuario, contrasenaHash, idPerfil, estado, fechaCreacion, fechaUltimaModificacion)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$idPersona, $nombreUsuario, $contrasena, $idPerfil, $estado]);
            header('Location: usuarios.php?success=create');
        }
        exit;
    }
}

// Manejo de operaciones GET (eliminar, ver detalles, búsqueda)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action == 'delete' && isset($_GET['id'])) {
        $pdo->prepare("DELETE FROM usuarios WHERE idUsuario = ?")->execute([$_GET['id']]);
        header('Location: usuarios.php?success=delete');
        exit;
    }
    
    if ($action == 'search') {
        $query = $_GET['query'] ?? '';
        $rol = $_GET['rol'] ?? '';
        
        $sql = "SELECT u.idUsuario, u.nombreUsuario, u.estado, 
                p.idPersona, p.cedula, p.primerNombre, p.segundoNombre, p.tercerNombre, 
                p.primerApellido, p.segundoApellido, pr.nombrePerfil
                FROM usuarios u
                JOIN perfiles pr ON u.idPerfil = pr.idPerfil
                JOIN personas p ON u.idPersona = p.idPersona
                WHERE 1=1";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (p.cedula LIKE ? OR p.primerNombre LIKE ? OR p.segundoNombre LIKE ? OR p.tercerNombre LIKE ? OR p.primerApellido LIKE ? OR p.segundoApellido LIKE ? OR u.nombreUsuario LIKE ?)";
            $search = "%$query%";
            $params = array_fill(0, 7, $search);
        }

        if (!empty($rol)) {
            $sql .= " AND u.idPerfil = ?";
            $params[] = $rol;
        }

        $sql .= " ORDER BY u.idUsuario DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre de usuario</th>
                        <th>Empleado</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $usr): ?>
                        <tr>
                            <td><?= $usr['nombreUsuario'] ?></td>
                            <td><?= $usr['cedula'] ?> - <?= $usr['primerNombre'] ?></td>
                            <td><?= $usr['nombrePerfil'] ?></td>
                            <td>
                                <span class="badge bg-<?= $usr['estado'] == 'Activo' ? 'success' : ($usr['estado'] == 'Inactivo' ? 'warning' : 'danger') ?>">
                                    <?= $usr['estado'] ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info me-1" onclick="verUsuario(<?= $usr['idUsuario'] ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="usuarios.php?action=edit&id=<?= $usr['idUsuario'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="usuarios.php?action=delete&id=<?= $usr['idUsuario'] ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('¿Está seguro?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        exit;
    }

    if ($action == 'view' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("
            SELECT u.idUsuario, u.nombreUsuario, u.estado, u.fechaCreacion,
            p.idPersona, p.cedula, p.primerNombre, p.segundoNombre, p.tercerNombre, 
            p.primerApellido, p.segundoApellido, p.fechaNacimiento, p.genero, 
            p.direccion, p.correoElectronico, pr.nombrePerfil
            FROM usuarios u
            JOIN perfiles pr ON u.idPerfil = pr.idPerfil
            JOIN personas p ON u.idPersona = p.idPersona
            WHERE u.idUsuario = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <h5 class="mb-3">Detalles del usuario</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted">Información del empleado</h6>
                        <p><strong>Cedula:</strong> <?= $usuario['cedula'] ?></p>
                        <p><strong>Nombre completo:</strong> 
                            <?= $usuario['primerNombre'] . ($usuario['segundoNombre'] ? ' '.$usuario['segundoNombre'] : '') . ($usuario['tercerNombre'] ? ' '.$usuario['tercerNombre'] : '') ?>
                        </p>
                        <p><strong>Apellido completo:</strong> 
                            <?= $usuario['primerApellido'] . ($usuario['segundoApellido'] ? ' '.$usuario['segundoApellido'] : '') ?>
                        </p>
                        <p><strong>Fecha de nacimiento:</strong> <?= $usuario['fechaNacimiento'] ?></p>
                        <p><strong>Género:</strong> <?= $usuario['genero'] ?></p>
                        <p><strong>Dirección:</strong> <?= $usuario['direccion'] ?></p>
                        <p><strong>Email:</strong> <?= $usuario['correoElectronico'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted">Información del sistema</h6>
                        <p><strong>Nombre de usuario:</strong> <?= $usuario['nombreUsuario'] ?></p>
                        <p><strong>Rol:</strong> <?= $usuario['nombrePerfil'] ?></p>
                        <p><strong>Estado:</strong> <?= $usuario['estado'] ?></p>
                        <p><strong>Creado:</strong> <?= $usuario['fechaCreacion'] ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        exit;
    }
}

// Manejo de búsqueda de empleados (AJAX integrado)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'search_employee') {
    $query = $_GET['query'] ?? '';
    $empleados = [];

    if ($query) {
        $stmt = $pdo->prepare("SELECT p.idPersona, p.cedula, 
                               CONCAT(p.primerNombre, ' ', p.primerApellido) as nombre
                               FROM personas p
                               LEFT JOIN usuarios u ON p.idPersona = u.idPersona
                               WHERE p.cedula IS NOT NULL 
                               AND u.idUsuario IS NULL
                               AND (p.cedula LIKE ? OR p.primerNombre LIKE ? OR p.primerApellido LIKE ?)
                               ORDER BY p.primerNombre ASC");
        $search = "%$query%";
        $stmt->execute([$search, $search, $search]);
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($empleados);
    exit;
}

// Alertas de éxito
$alertas = [
    'create' => ['mensaje' => 'Usuario creado exitosamente', 'tipo' => 'success'],
    'edit' => ['mensaje' => 'Usuario actualizado exitosamente', 'tipo' => 'success'],
    'delete' => ['mensaje' => 'Usuario eliminado exitosamente', 'tipo' => 'success'],
    'empleado_usado' => ['mensaje' => 'El empleado ya tiene un usuario asociado', 'tipo' => 'danger']
];
?>


<body>
    <?php require_once 'partials/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php require_once 'partials/sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Toast notifications -->
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
                    <div id="liveToast" class="toast" role="alert" aria-live="polite" aria-atomic="true" data-bs-delay="5000">
                        <div class="toast-header">
                            <strong class="me-auto">Sistema</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body" id="toast-body">
                            <!-- Mensaje dinámico -->
                        </div>
                    </div>
                </div>

                <!-- Mostrar toast si hay éxito -->
                <?php if (isset($_GET['success']) || isset($_GET['error'])): 
                    $clave = isset($_GET['success']) ? $_GET['success'] : 'empleado_usado';
                    $tipo = $alertas[$clave]['tipo'];
                    $mensaje = $alertas[$clave]['mensaje'];
                ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var toast = new bootstrap.Toast(document.getElementById('liveToast'));
                            document.querySelector('#toast-body').textContent = "<?= $mensaje ?>";
                            toast.show();
                        });
                    </script>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2">
                        <?= $action == 'list' ? 'Gestion de usuarios' : ($action == 'create' ? 'Nuevo usuario' : 'Editar usuario') ?>
                    </h1>
                    <a href="usuarios.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>

                <?php if ($action == 'list'): ?>
                    <!-- Filtros -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" id="searchUserInput" class="form-control" placeholder="Buscar por nombre o usuario...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <select id="filterRole" class="form-select">
                                        <option value="">Todos los roles</option>
                                        <?php 
                                        $roles = $pdo->query("SELECT * FROM perfiles")->fetchAll(PDO::FETCH_ASSOC);
                                        foreach($roles as $rol): ?>
                                            <option value="<?= $rol['idPerfil'] ?>"><?= $rol['nombrePerfil'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-secondary w-100" type="button" 
                                            onclick="document.getElementById('searchUserInput').value='';
                                                  document.getElementById('filterRole').value='';
                                                  buscarUsuarios();">
                                        <i class="bi bi-x-circle"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de usuarios -->
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Lista de usuarios</h5>
                            <a href="usuarios.php?action=create" class="btn btn-primary">
                                <i class="bi bi-person-add"></i> Nuevo usuario
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div id="usuariosContainer">
                                <?php
                                $usuarios = $pdo->query("
                                    SELECT u.idUsuario, u.nombreUsuario, u.estado, 
                                    p.idPersona, p.cedula, p.primerNombre, p.segundoNombre, p.tercerNombre, 
                                    p.primerApellido, p.segundoApellido, pr.nombrePerfil
                                    FROM usuarios u
                                    JOIN perfiles pr ON u.idPerfil = pr.idPerfil
                                    JOIN personas p ON u.idPersona = p.idPersona
                                    ORDER BY u.idUsuario DESC")->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nombre de usuario</th>
                                                <th>Empleado</th>
                                                <th>Rol</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($usuarios as $usr): ?>
                                                <tr>
                                                    <td><?= $usr['nombreUsuario'] ?></td>
                                                    <td><?= $usr['cedula'] ?> - <?= $usr['primerNombre'] ?></td>
                                                    <td><?= $usr['nombrePerfil'] ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $usr['estado'] == 'Activo' ? 'success' : ($usr['estado'] == 'Inactivo' ? 'warning' : 'danger') ?>">
                                                            <?= $usr['estado'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-info me-1" onclick="verUsuario(<?= $usr['idUsuario'] ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <a href="usuarios.php?action=edit&id=<?= $usr['idUsuario'] ?>" 
                                                           class="btn btn-sm btn-outline-primary me-1">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="usuarios.php?action=delete&id=<?= $usr['idUsuario'] ?>" 
                                                           class="btn btn-sm btn-outline-danger" 
                                                           onclick="return confirm('¿Está seguro?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                
                <?php else: // Formulario crear/editar ?>
                    <?php 
                    $usuario = null;
                    $empleados = $pdo->query("
                        SELECT p.idPersona, p.cedula, 
                        CONCAT(p.primerNombre, ' ', p.primerApellido) as nombre
                        FROM personas p
                        LEFT JOIN usuarios u ON p.idPersona = u.idPersona
                        WHERE p.cedula IS NOT NULL AND u.idUsuario IS NULL
                        ORDER BY p.primerNombre")->fetchAll(PDO::FETCH_ASSOC);
                        
                    if ($action == 'edit' && isset($_GET['id'])) {
                        $stmt = $pdo->prepare("
                            SELECT u.*, p.cedula, p.primerNombre, p.segundoNombre, p.tercerNombre, 
                            p.primerApellido, p.segundoApellido
                            FROM usuarios u
                            JOIN personas p ON u.idPersona = p.idPersona
                            WHERE u.idUsuario = ?");
                        $stmt->execute([$_GET['id']]);
                        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    ?>
                    
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><?= $usuario ? 'Editar' : 'Crear' ?> usuario</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="usuarios.php?action=<?= $action ?>">
                                <input type="hidden" name="action" value="save">
                                <?php if ($usuario): ?>
                                    <input type="hidden" name="idUsuario" value="<?= $usuario['idUsuario'] ?>">
                                <?php endif; ?>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Empleado</label>
                                            <?php if ($usuario): ?>
                                                <input type="text" class="form-control" value="<?= $usuario['cedula'] ?> - <?= $usuario['primerNombre'] ?>" readonly>
                                                <input type="hidden" name="idPersona" value="<?= $usuario['idPersona'] ?>">
                                            <?php else: ?>
                                            
                                                <ul id="resultadosBusqueda" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;"></ul>
                                                <select name="idPersona" id="empleadoSelect" class="form-control mt-3" required>
                                                    <option value="">Seleccione un empleado</option>
                                                    <?php foreach($empleados as $emp): ?>
                                                        <option value="<?= $emp['idPersona'] ?>" 
                                                            data-cedula="<?= $emp['cedula'] ?>"
                                                            data-nombre="<?= $emp['nombre'] ?>">
                                                            <?= $emp['cedula'] ?> - <?= $emp['nombre'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nombre de usuario</label>
                                            <input type="text" class="form-control" name="nombreUsuario" required
                                                   value="<?= $usuario['nombreUsuario'] ?? '' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Contraseña</label>
                                            <input type="text" class="form-control" name="contrasena" required
                                                   value="<?= $usuario['contrasenaHash'] ?? '' ?>">
                                            <small class="form-text text-muted">No se encripta para este caso</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Rol</label>
                                            <select class="form-control" name="idPerfil" required>
                                                <option value="">Seleccione un rol</option>
                                                <?php 
                                                $roles = $pdo->query("SELECT * FROM perfiles")->fetchAll(PDO::FETCH_ASSOC);
                                                foreach($roles as $rol): ?>
                                                    <option value="<?= $rol['idPerfil'] ?>" 
                                                        <?= ($usuario['idPerfil'] ?? '') == $rol['idPerfil'] ? 'selected' : '' ?>>
                                                        <?= $rol['nombrePerfil'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Estado</label>
                                            <select class="form-control" name="estado" required>
                                                <option value="Activo" <?= $usuario['estado'] == 'Activo' ? 'selected' : '' ?>>Activo</option>
                                                <option value="Inactivo" <?= $usuario['estado'] == 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                                <option value="Bloqueado" <?= $usuario['estado'] == 'Bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                                                <option value="Pendiente Aprobacion" <?= $usuario['estado'] == 'Pendiente Aprobacion' ? 'selected' : '' ?>>Pendiente aprobación</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <?= $usuario ? 'Actualizar' : 'Crear' ?> usuario
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Modal para ver detalles -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel">Detalles del usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="userDetails">
                    <!-- Detalles cargados con AJAX -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script> 

    <script>
        // Función de búsqueda de usuarios
        function buscarUsuarios() {
            const query = document.getElementById('searchUserInput').value;
            const rol = document.getElementById('filterRole').value;
            fetch(`usuarios.php?action=search&query=${encodeURIComponent(query)}&rol=${encodeURIComponent(rol)}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('usuariosContainer').innerHTML = html;
                });
        }

        // Ejecutar búsqueda al cargar
        buscarUsuarios();

        // Ejecutar búsqueda al escribir o cambiar filtro
        document.getElementById('searchUserInput').addEventListener('input', buscarUsuarios);
        document.getElementById('filterRole').addEventListener('change', buscarUsuarios);

        // Cargar detalles del usuario
        function verUsuario(id) {
            fetch(`usuarios.php?action=view&id=${id}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('userDetails').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('viewUserModal')).show();
                });
        }

        // Búsqueda dinámica de empleados
        document.getElementById('buscadorEmpleado')?.addEventListener('input', function () {
            const query = this.value;
            const resultados = document.getElementById('resultadosBusqueda');
            resultados.innerHTML = '';
            
            if (query.length < 2) {
                resultados.classList.remove('show');
                return;
            }
            
            fetch(`usuarios.php?action=search_employee&query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(empleados => {
                    resultados.classList.add('show');
                    empleados.forEach(emp => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action';
                        li.innerHTML = `<strong>${emp.cedula}</strong> - ${emp.nombre}`;
                        li.onclick = () => {
                            document.getElementById('empleadoSelect').value = emp.idPersona;
                            document.getElementById('buscadorEmpleado').value = `${emp.cedula} - ${emp.nombre}`;
                            resultados.innerHTML = '';
                        };
                        resultados.appendChild(li);
                    });
                });
        });
    </script>
</body>
</html>