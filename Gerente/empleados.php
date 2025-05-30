<?php 

require_once '../Administrador/Bd/conexion.php';

// Manejo de operaciones CRUD y AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'save') {
        $idPersona = $_POST['idPersona'] ?? null;
        $cedula = $_POST['cedula'];
        $primerNombre = $_POST['primerNombre'];
        $segundoNombre = $_POST['segundoNombre'];
        $tercerNombre = $_POST['tercerNombre'];
        $primerApellido = $_POST['primerApellido'];
        $segundoApellido = $_POST['segundoApellido'];
        $fechaNacimiento = $_POST['fechaNacimiento'];
        $genero = $_POST['genero'];
        $direccion = $_POST['direccion'];
        $correoElectronico = $_POST['correoElectronico'];
        
        if ($idPersona) {
            $stmt = $pdo->prepare("UPDATE personas SET 
                cedula = ?, primerNombre = ?, segundoNombre = ?, tercerNombre = ?,
                primerApellido = ?, segundoApellido = ?, fechaNacimiento = ?, genero = ?,
                direccion = ?, correoElectronico = ?, fechaUltimaModificacion = NOW()
                WHERE idPersona = ?");
            $stmt->execute([$cedula, $primerNombre, $segundoNombre, $tercerNombre,
                           $primerApellido, $segundoApellido, $fechaNacimiento, $genero,
                           $direccion, $correoElectronico, $idPersona]);
            header('Location: empleados.php?success=edit');
        } else {
            $stmt = $pdo->prepare("INSERT INTO personas 
                (cedula, primerNombre, segundoNombre, tercerNombre, primerApellido, 
                 segundoApellido, fechaNacimiento, genero, direccion, correoElectronico, 
                 fechaCreacion, fechaUltimaModificacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$cedula, $primerNombre, $segundoNombre, $tercerNombre,
                           $primerApellido, $segundoApellido, $fechaNacimiento, $genero,
                           $direccion, $correoElectronico]);
            header('Location: empleados.php?success=create');
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action == 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $pdo->prepare("DELETE FROM personas WHERE idPersona = ?")->execute([$id]);
        header('Location: empleados.php?success=delete');
        exit;
    }
    
    if ($action == 'search') {
        $query = $_GET['query'] ?? '';
        $stmt = $pdo->prepare("SELECT p.*, 
                              GROUP_CONCAT(t.numeroTelefono SEPARATOR ', ') as telefonos
                              FROM personas p
                              LEFT JOIN personatelefonos t ON p.idPersona = t.idPersona
                              WHERE p.cedula IS NOT NULL 
                              AND (p.cedula LIKE ? OR p.primerNombre LIKE ? OR p.segundoNombre LIKE ? OR p.tercerNombre LIKE ? OR p.primerApellido LIKE ? OR p.segundoApellido LIKE ?)
                              GROUP BY p.idPersona
                              ORDER BY p.idPersona DESC");
        $search = "%$query%";
        $stmt->execute(array_fill(0, 6, $search));
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre completo</th>
                        <th>Apellido</th>
                        <th>Correo</th>
                        <th>Teléfono(s)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($empleados as $emp): ?>
                        <tr>
                            <td><?= $emp['cedula'] ?></td>
                            <td><?= $emp['primerNombre'] . ($emp['segundoNombre'] ? ' '.$emp['segundoNombre'] : '') . ($emp['tercerNombre'] ? ' '.$emp['tercerNombre'] : '') ?></td>
                            <td><?= $emp['primerApellido'] . ($emp['segundoApellido'] ? ' '.$emp['segundoApellido'] : '') ?></td>
                            <td><?= $emp['correoElectronico'] ?></td>
                            <td><?= $emp['telefonos'] ?? 'No registrado' ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info me-1" onclick="verEmpleado(<?= $emp['idPersona'] ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="empleados.php?action=edit&id=<?= $emp['idPersona'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="empleados.php?action=delete&id=<?= $emp['idPersona'] ?>" 
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
        $stmt = $pdo->prepare("SELECT p.*, 
                               GROUP_CONCAT(t.numeroTelefono SEPARATOR ', ') as telefonos
                               FROM personas p
                               LEFT JOIN personatelefonos t ON p.idPersona = t.idPersona
                               WHERE p.idPersona = ?
                               GROUP BY p.idPersona");
        $stmt->execute([$id]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <h5 class="mb-3">Detalles del empleado</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted">Datos personales</h6>
                        <p><strong>Cédula:</strong> <?= $empleado['cedula'] ?></p>
                        <p><strong>Nombre completo:</strong> 
                            <?= $empleado['primerNombre'] . ($empleado['segundoNombre'] ? ' '.$empleado['segundoNombre'] : '') . ($empleado['tercerNombre'] ? ' '.$empleado['tercerNombre'] : '') ?>
                        </p>
                        <p><strong>Apellido completo:</strong> 
                            <?= $empleado['primerApellido'] . ($empleado['segundoApellido'] ? ' '.$empleado['segundoApellido'] : '') ?>
                        </p>
                        <p><strong>Fecha de nacimiento:</strong> <?= $empleado['fechaNacimiento'] ?></p>
                        <p><strong>Género:</strong> <?= $empleado['genero'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted">Datos de contacto</h6>
                        <p><strong>Dirección:</strong> <?= $empleado['direccion'] ?></p>
                        <p><strong>Email:</strong> <?= $empleado['correoElectronico'] ?></p>
                        <p><strong>Teléfonos:</strong> <?= $empleado['telefonos'] ?? 'No registrado' ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        exit;
    }
}

$action = $_GET['action'] ?? 'list';

$alertas = [
    'create' => ['mensaje' => 'Empleado creado exitosamente', 'tipo' => 'success'],
    'edit' => ['mensaje' => 'Empleado actualizado exitosamente', 'tipo' => 'success'],
    'delete' => ['mensaje' => 'Empleado eliminado exitosamente', 'tipo' => 'success']
];
?>

<?php require_once 'partials/header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php require_once 'partials/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <!-- Toast notifications -->
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
                <div id="liveToast" class="toast" role="alert" aria-live="polite" aria-atomic="true" data-bs-delay="5000">
                    <div class="toast-header">
                        <strong class="me-auto">Sistema POS</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body" id="toast-body">
                        <!-- Mensaje dinámico -->
                    </div>
                </div>
            </div>

            <!-- Mostrar toast si hay éxito -->
            <?php if (isset($_GET['success'])): 
                $tipo = $alertas[$_GET['success']]['tipo'];
                $mensaje = $alertas[$_GET['success']]['mensaje'];
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
                    <?= $action == 'list' ? 'Gestion de empleados' : ($action == 'create' ? 'Nuevo empleado' : 'Editar empleado') ?>
                </h1>
                <a href="empleados.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <?php if ($action == 'list'): ?>
                <!-- Buscador -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre, apellido o cédula...">
                            <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('searchInput').value=''; buscarEmpleados();">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de empleados -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Lista de empleados</h5>
                        <a href="empleados.php?action=create" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Nuevo empleado
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div id="empleadosContainer">
                            <?php
                            $empleados = $pdo->query("SELECT p.*, 
                                                    GROUP_CONCAT(t.numeroTelefono SEPARATOR ', ') as telefonos
                                                    FROM personas p
                                                    LEFT JOIN personatelefonos t ON p.idPersona = t.idPersona
                                                    WHERE p.cedula IS NOT NULL 
                                                    GROUP BY p.idPersona
                                                    ORDER BY p.idPersona DESC")->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cédula</th>
                                            <th>Nombre completo</th>
                                            <th>Apellido</th>
                                            <th>Correo</th>
                                            <th>Teléfono(s)</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($empleados as $emp): ?>
                                            <tr>
                                                <td><?= $emp['cedula'] ?></td>
                                                <td><?= $emp['primerNombre'] . ($emp['segundoNombre'] ? ' '.$emp['segundoNombre'] : '') . ($emp['tercerNombre'] ? ' '.$emp['tercerNombre'] : '') ?></td>
                                                <td><?= $emp['primerApellido'] . ($emp['segundoApellido'] ? ' '.$emp['segundoApellido'] : '') ?></td>
                                                <td><?= $emp['correoElectronico'] ?></td>
                                                <td><?= $emp['telefonos'] ?? 'No registrado' ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info me-1" onclick="verEmpleado(<?= $emp['idPersona'] ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <a href="empleados.php?action=edit&id=<?= $emp['idPersona'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="empleados.php?action=delete&id=<?= $emp['idPersona'] ?>" 
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
                $empleado = null;
                if ($action == 'edit' && isset($_GET['id'])) {
                    $stmt = $pdo->prepare("SELECT p.* 
                                          FROM personas p
                                          WHERE p.idPersona = ?");
                    $stmt->execute([$_GET['id']]);
                    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                ?>
                
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><?= $empleado ? 'Editar' : 'Crear' ?> empleado</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="empleados.php?action=<?= $action ?>">
                            <input type="hidden" name="action" value="save">
                            <?php if ($empleado): ?>
                                <input type="hidden" name="idPersona" value="<?= $empleado['idPersona'] ?>">
                            <?php endif; ?>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Cédula</label>
                                        <input type="text" class="form-control" name="cedula" required 
                                               value="<?= $empleado['cedula'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Fecha de nacimiento</label>
                                        <input type="date" class="form-control" name="fechaNacimiento" required
                                               value="<?= $empleado['fechaNacimiento'] ?? date('Y-m-d') ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Primer nombre</label>
                                        <input type="text" class="form-control" name="primerNombre" required
                                               value="<?= $empleado['primerNombre'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Segundo nombre</label>
                                        <input type="text" class="form-control" name="segundoNombre"
                                               value="<?= $empleado['segundoNombre'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Tercer nombre</label>
                                        <input type="text" class="form-control" name="tercerNombre"
                                               value="<?= $empleado['tercerNombre'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Primer apellido</label>
                                        <input type="text" class="form-control" name="primerApellido" required
                                               value="<?= $empleado['primerApellido'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Segundo apellido</label>
                                        <input type="text" class="form-control" name="segundoApellido"
                                               value="<?= $empleado['segundoApellido'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Género</label>
                                        <select class="form-select" name="genero">
                                            <option value="">Seleccione...</option>
                                            <option value="Masculino" <?= $empleado['genero'] == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                            <option value="Femenino" <?= $empleado['genero'] == 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                                            <option value="Otro" <?= $empleado['genero'] == 'Otro' ? 'selected' : '' ?>>Otro</option>
                                            <option value="Prefiero no decirlo" <?= $empleado['genero'] == 'Prefiero no decirlo' ? 'selected' : '' ?>>Prefiero no decirlo</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Dirección</label>
                                        <input type="text" class="form-control" name="direccion"
                                               value="<?= $empleado['direccion'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Correo electrónico</label>
                                        <input type="email" class="form-control" name="correoElectronico"
                                               value="<?= $empleado['correoElectronico'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <?= $empleado ? 'Actualizar' : 'Crear' ?> empleado
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Modal para ver detalles -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewEmployeeModalLabel">Detalles del empleado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="employeeDetails">
        <!-- Detalles cargados con AJAX -->
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> 
<script>
// Función de búsqueda
function buscarEmpleados() {
    const query = document.getElementById('searchInput').value;
    fetch(`empleados.php?action=search&query=${encodeURIComponent(query)}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('empleadosContainer').innerHTML = html;
        });
}

// Ejecutar búsqueda al escribir
document.getElementById('searchInput').addEventListener('input', function() {
    buscarEmpleados();
});

// Cargar detalles del empleado
function verEmpleado(id) {
    fetch(`empleados.php?action=view&id=${id}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('employeeDetails').innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewEmployeeModal')).show();
        });
}
</script>
</body>
</html>