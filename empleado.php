<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados</title>
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

        .form-group input {
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
        <h1>Gestión de Empleados</h1>
        <form id="empleadoForm">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="numeroTelefono">Número de Teléfono</label>
                <input type="text" id="numeroTelefono" name="numeroTelefono" required>
            </div>
            <button type="submit">Añadir Empleado</button>
        </form>

        <h2>Lista de Empleados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>Número de Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="empleadosList">
                <!-- Aquí se agregarán los empleados dinámicamente -->
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const API_URL = 'http://31.220.97.169:5000/api/empleados';
        let editingId = null;

        document.addEventListener('DOMContentLoaded', () => {
            cargarEmpleados();

            document.getElementById('empleadoForm').addEventListener('submit', function (event) {
                event.preventDefault();
                let nombre = document.getElementById('nombre').value;
                let apellido = document.getElementById('apellido').value;
                let email = document.getElementById('email').value;
                let numeroTelefono = document.getElementById('numeroTelefono').value;

                if (editingId) {
                    // Si estamos editando, actualizamos el empleado
                    actualizarEmpleado(editingId, nombre, apellido, email, numeroTelefono);
                } else {
                    agregarEmpleado(nombre, apellido, email, numeroTelefono);
                }
            });
        });

        function cargarEmpleados() {
            fetch(API_URL)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta de la API');
                    }
                    return response.json();
                })
                .then(data => {
                    let empleadosList = document.getElementById('empleadosList');
                    empleadosList.innerHTML = '';

                    data.forEach(empleado => {
                        let row = `
                            <tr>
                                <td>${empleado.id}</td>
                                <td>${empleado.nombre}</td>
                                <td>${empleado.apellido}</td>
                                <td>${empleado.email}</td>
                                <td>${empleado.numero_telefono || 'N/A'}</td>
                                <td class="actions">
                                    <i class="fas fa-edit" title="Editar" onclick="cargarEmpleado(${empleado.id})"></i>
                                    <i class="fas fa-trash-alt" title="Eliminar" onclick="eliminarEmpleado(${empleado.id})"></i>
                                </td>
                            </tr>`;
                        empleadosList.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error al cargar empleados:', error);
                });
        }

        function agregarEmpleado(nombre, apellido, email, numeroTelefono) {
            fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    nombre, 
                    apellido, 
                    email, 
                    numeroTelefono 
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => { throw new Error(errorData.message); });
                }
                return response.json();
            })
            .then(data => {
                Swal.fire('Éxito', 'Empleado añadido correctamente', 'success');
                cargarEmpleados();
                document.getElementById('empleadoForm').reset();
                editingId = null;  // Resetear el ID de edición
            })
            .catch(error => {
                console.error('Error al añadir empleado:', error);
                Swal.fire('Error', `Hubo un problema al añadir el empleado: ${error.message}`, 'error');
            });
        }

        function eliminarEmpleado(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarlo',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${API_URL}/${id}`, {
                        method: 'DELETE',
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error al eliminar el empleado');
                        }
                        return response.json();
                    })
                    .then(data => {
                        Swal.fire('Eliminado', 'El empleado ha sido eliminado.', 'success');
                        cargarEmpleados();
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Hubo un problema al eliminar el empleado.', 'error');
                    });
                }
            });
        }

        // Cargar datos del empleado para editar
        function cargarEmpleado(id) {
            fetch(`${API_URL}/${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al cargar el empleado');
                    }
                    return response.json();
                })
                .then(empleado => {
                    // Cargar los datos en el formulario
                    document.getElementById('nombre').value = empleado.nombre;
                    document.getElementById('apellido').value = empleado.apellido;
                    document.getElementById('email').value = empleado.email;
                    document.getElementById('numeroTelefono').value = empleado.numeroTelefono;
                    editingId = empleado.id;  // Guardar el ID para actualizar más tarde
                    document.querySelector('button[type="submit"]').textContent = 'Actualizar Empleado';  // Cambiar el texto del botón
                })
                .catch(error => {
                    Swal.fire('Error', 'Hubo un problema al cargar el empleado.', 'error');
                });
        }

        // Actualizar empleado
    function actualizarEmpleado(id, nombre, apellido, email, numeroTelefono) {
    fetch(`http://31.220.97.169:5000/api/empleados/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            nombre,
            apellido,
            email,
            numeroTelefono
        })
    })
    .then(response => {
        console.log('Response:', response); // Log de la respuesta
        if (!response.ok) {
            return response.json().then(errorData => { throw new Error(errorData.message); });
        }
        return response.json();
    })
    .then(data => {
        Swal.fire('Éxito', 'Empleado actualizado correctamente', 'success');
        cargarEmpleados();
        document.getElementById('empleadoForm').reset();
        editingId = null;  // Resetear el ID de edición
        document.querySelector('button[type="submit"]').textContent = 'Añadir Empleado';  // Restablecer el texto del botón
    })
    .catch(error => {
        console.error('Error al actualizar empleado:', error);
        Swal.fire('Error', `Hubo un problema al actualizar el empleado: ${error.message}`, 'error');
    });
}


    </script>
</body>
</html>


