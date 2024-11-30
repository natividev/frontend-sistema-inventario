<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gesti�n de Inventario</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Gestion de Inventario</h1>
        <!-- Campos de formulario -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="sedeId" class="form-label">Sede</label>
                <select id="sedeId" class="form-control"></select>
            </div>
            <div class="col-md-4">
                <label for="edificioId" class="form-label">Edificio</label>
                <select id="edificioId" class="form-control"></select>
            </div>
            <div class="col-md-4">
                <label for="nivelId" class="form-label">Nivel</label>
                <select id="nivelId" class="form-control"></select>
            </div>
        </div>
        <!-- M�s campos de formulario -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="areaId" class="form-label">Area</label>
                <select id="areaId" class="form-control"></select>
            </div>
            <div class="col-md-4">
                <label for="equipoId" class="form-label">Equipo</label>
                <select id="equipoId" class="form-control"></select>
            </div>
            <div class="col-md-4">
                <label for="especificacionesId" class="form-label">Especificacion</label>
                <select id="especificacionesId" class="form-control"></select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="direccionIp" class="form-label">Direccion IP</label>
                <input type="text" id="direccionIp" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="estado" class="form-label">Estado</label>
                <select id="estado" class="form-control">
                    <option value="ASIGNADO">ASIGNADO</option>
                    <option value="DESCARTE">DESCARTE</option>
                    <option value="BODEGA">BODEGA</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="administrativo" class="form-label">Administrativo</label>
                <input type="checkbox" id="administrativo">
            </div>
        </div>
        <!-- M�s campos -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="academico" class="form-label">Academico</label>
                <input type="checkbox" id="academico">
            </div>
            <div class="col-md-4">
                <label for="empleadoId" class="form-label">Empleados</label>
                <select id="empleadoId" class="form-control"></select>
            </div>
        </div>
        <!-- Fechas -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="fechaIngresoEquipo" class="form-label">Fecha Ingreso Equipo</label>
                <input type="date" id="fechaIngresoEquipo" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="fechaAsignado" class="form-label">Fecha Asignado</label>
                <input type="date" id="fechaAsignado" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="fechaDescarte" class="form-label">Fecha Descarte</label>
                <input type="date" id="fechaDescarte" class="form-control">
            </div>
        </div>
        <!-- Bot�n para agregar -->
        <button type="button" id="agregarInventarioBtn" class="btn btn-primary btn-block" onclick="agregarInventario()">Agregar Inventario</button>

        <!-- Tabla para mostrar inventarios -->
        <table class="table table-hover mt-5">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th><th>Sede</th><th>Edificio</th><th>Nivel</th><th>Area</th>
                    <th>Equipo</th><th>Especificacion</th><th>Direccion IP</th><th>Estado</th><th>Empleado</th>
                    <th>Administrativo</th><th>Academico</th><th>Fecha Ingreso</th><th>Fecha Asignado</th><th>Fecha Descarte</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody id="inventarioTableBody"></tbody>
        </table>
    </div>

    <script>
        const API_BASE_URL = 'http://localhost:5001/api/';

        async function cargarDatosIniciales() {
            try {
                await Promise.all([
                    cargarSelect('sede', 'sedeId'),
                    cargarSelect('edificio', 'edificioId'),
                    cargarSelect('nivel', 'nivelId'),
                    cargarSelect('area', 'areaId'),
                    cargarSelect('empleados', 'empleadoId'),
                    cargarSelect('equipo', 'equipoId'),
                    cargarSelect('especificaciones', 'especificacionesId')
                ]);
            } catch (error) {
                Swal.fire('Error', `Problema al cargar datos iniciales: ${error.message}`, 'error');
            }
        }

        async function cargarSelect(endpoint, selectId) {
            try {
                const response = await fetch(`${API_BASE_URL}${endpoint}`);
                if (!response.ok) throw new Error(`Error al cargar ${endpoint}`);
                const data = await response.json();
                const select = document.getElementById(selectId);
                select.innerHTML = '<option value="">Seleccione una opcion</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.nombre || item.descripcion || item.tipo;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error(`Error al cargar el select ${selectId}: ${error.message}`);
            }
        }

       async function agregarInventario() {
    const inventario = {
        sedeId: document.getElementById('sedeId').value,
        edificioId: document.getElementById('edificioId').value,
        nivelId: document.getElementById('nivelId').value,
        areaId: document.getElementById('areaId').value,
        equipoId: document.getElementById('equipoId').value,
        especificacionId: document.getElementById('especificacionesId').value,
        empleadoId: document.getElementById('empleadoId').value,
        direccionIp: document.getElementById('direccionIp').value,
        estado: document.getElementById('estado').value,
        administrativo: document.getElementById('administrativo').checked,
        academico: document.getElementById('academico').checked,
        fechaIngresoEquipo: document.getElementById('fechaIngresoEquipo').value,
        fechaAsignado: document.getElementById('fechaAsignado').value,
        fechaDescarte: document.getElementById('fechaDescarte').value ?? null,
    };

    // Validaci�n de campos obligatorios
    if (!inventario.sedeId || !inventario.edificioId || !inventario.nivelId || !inventario.areaId ||
        !inventario.equipoId || !inventario.especificacionId || !inventario.empleadoId || !inventario.direccionIp || !inventario.fechaIngresoEquipo) {
        Swal.fire('Advertencia', 'Complete todos los campos obligatorios antes de continuar.', 'warning');
        return;
    }

    console.log("Datos a enviar:", inventario);

    try {
        const response = await fetch(`${API_BASE_URL}inventario`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(inventario),
        });

        if (!response.ok) {
            const errorData = await response.json();
            console.log("Error en la respuesta de la API:", errorData);
            throw new Error(errorData.message || 'No se pudo agregar el inventario');
        }

        Swal.fire('Exito', 'Inventario agregado exitosamente', 'success');
        cargarInventarioTabla();
        
        // Limpiar los campos del formulario despu�s de agregar el inventario
        document.getElementById('direccionIp').value = '';
        document.getElementById('fechaIngresoEquipo').value = '';
        document.getElementById('fechaAsignado').value = '';
        document.getElementById('fechaDescarte').value = '';
        document.getElementById('administrativo').checked = false;
        document.getElementById('academico').checked = false;
        document.getElementById('sedeId').selectedIndex = 0;
        document.getElementById('edificioId').selectedIndex = 0;
        document.getElementById('nivelId').selectedIndex = 0;
        document.getElementById('areaId').selectedIndex = 0;
        document.getElementById('equipoId').selectedIndex = 0;
        document.getElementById('especificacionesId').selectedIndex = 0;
        document.getElementById('empleadoId').selectedIndex = 0;
        document.getElementById('estado').selectedIndex = 0;

    } catch (error) {
        Swal.fire('Error', `Error al agregar inventario: ${error.message}`, 'error');
    }
}








        async function cargarInventarioTabla() {
            try {
                const response = await fetch(`${API_BASE_URL}inventario`);
                if (!response.ok) throw new Error('Error al cargar inventario');
                const data = await response.json();


                const tableBody = document.getElementById('inventarioTableBody');
                tableBody.innerHTML = '';

                data.map(item => {
                    const row = tableBody.insertRow();
                    row.innerHTML = `
                        <td>${item.id}</td>
                        <td>${item.sede.nombre}</td>
                        <td>${item.edificio.nombre}</td>
                        <td>${item.nivel.nombre}</td>
                        <td>${item.area.nombre}</td>
                        <td>${item.equipo.nombre}</td>
                        <td>${item.especificacion.descripcion}</td>
                        <td>${item.direccion_ip}</td>
                        <td>${item.estado}</td>
                        <td>${item.empleado.nombre}</td>
                        <td>${item.administrativo ? 'Si' : 'No'}</td>
                        <td>${item.academico ? 'Si' : 'No'}</td>
                        <td>${item.fecha_ingreso_equipo}</td>
                        <td>${item.fecha_asignado}</td>
                        <td>${item.fecha_descarte}</td>
                        <td><button class="btn btn-danger btn-sm" onclick="eliminarInventario(${item.id})">Eliminar</button></td>
                    `;
                });
                
            } catch (error) {
                Swal.fire('Error', `Error al cargar la tabla de inventario: ${error.message}`, 'error');
            }
        }

        async function eliminarInventario(id) {
            try {
                const response = await fetch(`${API_BASE_URL}inventario/${id}`, { method: 'DELETE' });
                if (!response.ok) throw new Error('Error al eliminar inventario');
                Swal.fire('Eliminado', 'Inventario eliminado correctamente', 'success');
                cargarInventarioTabla();
            } catch (error) {
                Swal.fire('Error', `Error al eliminar el inventario: ${error.message}`, 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarDatosIniciales();
            cargarInventarioTabla();
        });
    </script>
</body>
</html>
