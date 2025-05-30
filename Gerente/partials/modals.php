<!-- Modal para ver detalles de empleado -->
<div class="modal fade" id="empleadoModal" tabindex="-1" aria-labelledby="empleadoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Detalles del Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-4">Cédula</dt>
                    <dd class="col-sm-8" id="modalCedula"></dd>
                    
                    <dt class="col-sm-4">Nombre Completo</dt>
                    <dd class="col-sm-8" id="modalNombreCompleto"></dd>
                    
                    <dt class="col-sm-4">Fecha Nacimiento</dt>
                    <dd class="col-sm-8" id="modalFechaNacimiento"></dd>
                    
                    <dt class="col-sm-4">Género</dt>
                    <dd class="col-sm-8" id="modalGenero"></dd>
                    
                    <dt class="col-sm-4">Dirección</dt>
                    <dd class="col-sm-8" id="modalDireccion"></dd>
                    
                    <dt class="col-sm-4">Correo Electrónico</dt>
                    <dd class="col-sm-8" id="modalCorreo"></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de usuario -->
<div class="modal fade" id="usuarioModal" tabindex="-1" aria-labelledby="usuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="usuarioModalTitulo">Detalles del Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-4">Empleado</dt>
                    <dd class="col-sm-8" id="usuarioEmpleado"></dd>
                    
                    <dt class="col-sm-4">Rol</dt>
                    <dd class="col-sm-8" id="usuarioRol"></dd>
                    
                    <dt class="col-sm-4">Estado</dt>
                    <dd class="col-sm-8" id="usuarioEstado"></dd>
                    
                    <dt class="col-sm-4">Última Conexión</dt>
                    <dd class="col-sm-8" id="usuarioFechaUltimaConexion"></dd>
                </dl>
            </div>
        </div>
    </div>
</div>