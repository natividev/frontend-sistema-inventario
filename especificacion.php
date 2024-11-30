<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Especificaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #00365E;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #00365E;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #004f8d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #E7E9EB;
            color: #00365E;
        }
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .actions i {
            color: #00365E;
            margin-right: 10px;
            cursor: pointer;
        }
        .actions i:hover {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Especificaciones</h1>
        <form id="especificacionForm">
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <input type="text" id="descripcion" name="descripcion" required>
            </div>
            <div class="form-group">
                <label for="modeloId">Seleccionar Modelo</label>
                <select id="modeloId" name="modeloId" required>
                    <option value="">Seleccionar modelo...</option>
                </select>
            </div>
            <div class="form-group">
                <label for="serie">Serie</label>
                <input type="text" id="serie" name="serie" required>
            </div>
            <div class="form-group">
                <label for="dimensiones">Dimensiones</label>
                <input type="text" id="dimensiones" name="dimensiones" required>
            </div>
            <div class="form-group">
                <label for="activoFijo">Activo Fijo</label>
                <select id="activoFijo" name="activoFijo" required>
                    <option value="true">Sí</option>
                    <option value="false">No</option>
                </select>
            </div>
            <div class="form-group">
                <label for="marcaId">Seleccionar Marca</label>
                <select id="marcaId" name="marcaId" required>
                    <option value="">Seleccionar marca...</option>
                </select>
            </div>
            <div class="form-group">
                <label for="empleadoId">Seleccionar Empleado</label>
                <select id="empleadoId" name="empleadoId" required>
                    <option value="">Seleccionar empleado...</option>
                </select>
            </div>
            <button type="submit">Añadir Especificación</button>
        </form>

        <h2>Lista de Especificaciones</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descripción</th>
                    <th>Modelo</th>
                    <th>Serie</th>
                    <th>Dimensiones</th>
                    <th>Activo Fijo</th>
                    <th>Marca</th>
                    <th>Empleado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="especificacionesList">
                <!-- Aquí se agregarán las especificaciones dinámicamente -->
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const API_URL_ESPECIFICACION = 'http://31.220.97.169:5000/api/especificaciones';
        const API_URL_MODELO = 'http://31.220.97.169:5000/api/modelo';
        const API_URL_MARCA = 'http://31.220.97.169:5000/api/marca';
        const API_URL_EMPLEADO = 'http://31.220.97.169:5000/api/empleados';

        document.addEventListener('DOMContentLoaded', () => {
            cargarModelos();
            cargarMarcas();
            cargarEmpleados();
            cargarEspecificaciones();

            document.getElementById('especificacionForm').addEventListener('submit', function (event) {
                event.preventDefault();
                const especificacion = {
                    descripcion: document.getElementById('descripcion').value,
                    modeloId: document.getElementById('modeloId').value,
                    serie: document.getElementById('serie').value,
                    dimensiones: document.getElementById('dimensiones').value,
                    activoFijo: document.getElementById('activoFijo').value === "true",
                    marcaId: document.getElementById('marcaId').value,
                    empleadoId: document.getElementById('empleadoId').value
                };

                if (this.dataset.editing) {
                    actualizarEspecificacion(this.dataset.editing, especificacion);
                } else {
                    agregarEspecificacion(especificacion);
                }
            });
        });

        function cargarModelos() {
            fetch(API_URL_MODELO)
                .then(response => response.json())
                .then(data => {
                    const modeloSelect = document.getElementById('modeloId');
                    data.forEach(modelo => {
                        const option = document.createElement('option');
                        option.value = modelo.id;
                        option.textContent = modelo.nombre;
                        modeloSelect.appendChild(option);
                    });
                });
        }

        function cargarMarcas() {
            fetch(API_URL_MARCA)
                .then(response => response.json())
                .then(data => {
                    const marcaSelect = document.getElementById('marcaId');
                    data.forEach(marca => {
                        const option = document.createElement('option');
                        option.value = marca.id;
                        option.textContent = marca.nombre;
                        marcaSelect.appendChild(option);
                    });
                });
        }

        function cargarEmpleados() {
            fetch(API_URL_EMPLEADO)
                .then(response => response.json())
                .then(data => {
                    const empleadoSelect = document.getElementById('empleadoId');
                    data.forEach(empleado => {
                        const option = document.createElement('option');
                        option.value = empleado.id;
                        option.textContent = empleado.nombre;
                        empleadoSelect.appendChild(option);
                    });
                });
        }

        function cargarEspecificaciones() {
            fetch(API_URL_ESPECIFICACION)
                .then(response => response.json())
                .then(data => {
                    const especificacionesList = document.getElementById('especificacionesList');
                    especificacionesList.innerHTML = ''; 

                    data.forEach(especificacion => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${especificacion.id}</td>
                            <td>${especificacion.descripcion}</td>
                            <td>${getModeloNombre(especificacion.modeloId)}</td>
                            <td>${especificacion.serie}</td>
                            <td>${especificacion.dimensiones}</td>
                            <td>${especificacion.activoFijo ? 'Sí' : 'No'}</td>
                            <td>${getMarcaNombre(especificacion.marcaId)}</td>
                            <td>${getEmpleadoNombre(especificacion.empleadoId)}</td>
                            <td class="actions">
                                <i class="fas fa-edit" onclick="editarEspecificacion(${especificacion.id})"></i>
                                <i class="fas fa-trash" onclick="eliminarEspecificacion(${especificacion.id})"></i>
                            </td>
                        `;
                        especificacionesList.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar especificaciones:', error);
                });
        }

        function agregarEspecificacion(especificacion) {
            fetch(API_URL_ESPECIFICACION, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(especificacion),
            })
                .then(response => response.json())
                .then(data => {
                    Swal.fire('¡Añadido!', 'Especificación añadida exitosamente.', 'success');
                    cargarEspecificaciones();
                    document.getElementById('especificacionForm').reset();
                })
                .catch(error => {
                    console.error('Error al añadir especificación:', error);
                    Swal.fire('Error', 'Hubo un problema al añadir la especificación.', 'error');
                });
        }

        function editarEspecificacion(id) {
            fetch(`${API_URL_ESPECIFICACION}/${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('descripcion').value = data.descripcion;
                    document.getElementById('modeloId').value = data.modeloId;
                    document.getElementById('serie').value = data.serie;
                    document.getElementById('dimensiones').value = data.dimensiones;
                    document.getElementById('activoFijo').value = data.activoFijo ? 'true' : 'false';
                    document.getElementById('marcaId').value = data.marcaId;
                    document.getElementById('empleadoId').value = data.empleadoId;

                    document.getElementById('especificacionForm').dataset.editing = id;
                })
                .catch(error => {
                    console.error('Error al editar especificación:', error);
                });
        }

        function actualizarEspecificacion(id, especificacion) {
            fetch(`${API_URL_ESPECIFICACION}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(especificacion),
            })
                .then(response => response.json())
                .then(data => {
                    Swal.fire('¡Actualizado!', 'Especificación actualizada exitosamente.', 'success');
                    cargarEspecificaciones();
                    document.getElementById('especificacionForm').reset();
                    delete document.getElementById('especificacionForm').dataset.editing;
                })
                .catch(error => {
                    console.error('Error al actualizar especificación:', error);
                    Swal.fire('Error', 'Hubo un problema al actualizar la especificación.', 'error');
                });
        }

        function eliminarEspecificacion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'No podrás revertir esta acción.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${API_URL_ESPECIFICACION}/${id}`, {
                        method: 'DELETE',
                    })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire('¡Eliminado!', 'Especificación eliminada exitosamente.', 'success');
                            cargarEspecificaciones();
                        })
                        .catch(error => {
                            console.error('Error al eliminar especificación:', error);
                            Swal.fire('Error', 'Hubo un problema al eliminar la especificación.', 'error');
                        });
                }
            });
        }


        function getModeloNombre(modeloId) {
            // Aquí puedes añadir la lógica para obtener el nombre del modelo
            return `Modelo ${modeloId}`;
        }

        function getMarcaNombre(marcaId) {
            // Aquí puedes añadir la lógica para obtener el nombre de la marca
            return `Marca ${marcaId}`;
        }

        function getEmpleadoNombre(empleadoId) {
           // Aquí puedes añadir la lógica para obtener el nombre del empleado
            return `Empleado ${empleadoId}`;
        }
    </script>
</body>
</html>
