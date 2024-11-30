<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Equipos</title>
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

        .btn-export {
            margin-top: 10px;
            background-color: #00365E; /* Color verde */
        }

        .btn-export:hover {
            background-color: #00365E; /* Color verde más oscuro */
        }

        #search {
            margin-bottom: 20px;
            padding: 8px;
            width: 100%;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Gestión de Equipos</h1>

        <!-- Buscador -->
        <input type="text" id="search" placeholder="Buscar equipo..." onkeyup="filtrarEquipos()" />

        <form id="equipoForm">
            <div class="form-group">
                <label for="nombre">Nombre del Equipo</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="marcaId">Seleccionar Marca</label>
                <select id="marcaId" name="marcaId" required>
                    <option value="">Seleccionar marca...</option>
                    <!-- Aquí se llenará la lista de marcas -->
                </select>
            </div>
            <button type="submit">Añadir Equipo</button>
        </form>

        <button class="btn-export" onclick="exportarExcel()">Generar Excel</button> <!-- Botón para generar Excel -->

        <h2>Lista de Equipos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Marca</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="equiposList">
                <!-- Aquí se agregarán los equipos dinámicamente -->
            </tbody>
        </table>
    </div>

    <script>
        const API_URL_EQUIPO = 'http://31.220.97.169:5000/api/equipo'; // Asegúrate de que este sea el correcto
        const API_URL_MARCA = 'http://31.220.97.169:5000/api/marca'; // URL para obtener las marcas

        let equipos = []; // Para almacenar los equipos cargados

        document.addEventListener('DOMContentLoaded', () => {
            cargarMarcas();  // Cargar marcas al cargar la página
            cargarEquipos();  // Cargar equipos al cargar la página

            document.getElementById('equipoForm').addEventListener('submit', function (event) {
                event.preventDefault();
                const nombre = document.getElementById('nombre').value;
                const marcaId = document.getElementById('marcaId').value;
                if (this.dataset.editing) {
                    actualizarEquipo(this.dataset.editing);
                } else {
                    agregarEquipo(nombre, marcaId);
                }
            });
        });

        // Función para cargar las marcas desde la API
        function cargarMarcas() {
            fetch(API_URL_MARCA)
                .then(response => response.json())
                .then(data => {
                    const marcaSelect = document.getElementById('marcaId');
                    marcaSelect.innerHTML = '<option value="">Seleccionar marca...</option>'; // Resetea el dropdown

                    data.forEach(marca => {
                        const option = `<option value="${marca.id}">${marca.nombre}</option>`;
                        marcaSelect.innerHTML += option; // Agrega cada marca al dropdown
                    });
                })
                .catch(error => {
                    console.error('Error al cargar marcas:', error);
                });
        }

        // Función para cargar equipos desde la API
        function cargarEquipos() {
            fetch(API_URL_EQUIPO)
                .then(response => response.json())
                .then(data => {
                    equipos = data; // Guardamos los equipos en la variable global
                    mostrarEquipos(equipos); // Mostramos los equipos
                })
                .catch(error => {
                    console.error('Error al cargar equipos:', error);
                });
        }

        // Función para mostrar los equipos en la tabla
        function mostrarEquipos(equipos) {
            const equiposList = document.getElementById('equiposList');
            equiposList.innerHTML = '';

            equipos.forEach(equipo => {
                const row = `
                    <tr>
                        <td>${equipo.id}</td>
                        <td>${equipo.nombre}</td>
                        <td>${equipo.marca.nombre}</td> <!-- Mostrar el nombre de la marca -->
                        <td class="actions">
                            <i class="fas fa-edit" title="Editar" onclick="prepararEquipo(${equipo.id}, '${equipo.nombre}', ${equipo.marca.id})"></i>
                            <i class="fas fa-trash-alt" title="Eliminar" onclick="eliminarEquipo(${equipo.id})"></i>
                        </td>
                    </tr>`;
                equiposList.innerHTML += row;
            });
        }

        // Función para filtrar equipos
        function filtrarEquipos() {
            const searchTerm = document.getElementById('search').value.toLowerCase();
            const filteredEquipos = equipos.filter(equipo => {
                return equipo.nombre.toLowerCase().includes(searchTerm) || equipo.marca.nombre.toLowerCase().includes(searchTerm);
            });
            mostrarEquipos(filteredEquipos);
        }

        // Función para agregar un equipo
        function agregarEquipo(nombre, marcaId) {
            fetch(API_URL_EQUIPO, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre, marcaId: marcaId })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire('Éxito', 'Equipo agregado correctamente', 'success');
                cargarEquipos();
                document.getElementById('equipoForm').reset();
            })
            .catch(error => {
                console.error('Error al agregar equipo:', error);
                Swal.fire('Error', 'Hubo un problema al agregar el equipo.', 'error');
            });
        }

        // Función para preparar el equipo para edición
        function prepararEquipo(id, nombre, marcaId) {
            document.getElementById('nombre').value = nombre;
            document.getElementById('marcaId').value = marcaId;
            document.getElementById('equipoForm').dataset.editing = id; // Guardar el ID del equipo en el formulario
        }

        // Función para actualizar un equipo
        function actualizarEquipo(id) {
            const nombre = document.getElementById('nombre').value;
            const marcaId = document.getElementById('marcaId').value;

            fetch(`${API_URL_EQUIPO}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre, marcaId: marcaId })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire('Éxito', 'Equipo actualizado correctamente', 'success');
                cargarEquipos();
                document.getElementById('equipoForm').reset();
                delete document.getElementById('equipoForm').dataset.editing; // Limpiar el ID del formulario
            })
            .catch(error => {
                console.error('Error al actualizar equipo:', error);
                Swal.fire('Error', 'Hubo un problema al actualizar el equipo.', 'error');
            });
        }

        // Función para eliminar un equipo
        function eliminarEquipo(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Este equipo será eliminado permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${API_URL_EQUIPO}/${id}`, {
                        method: 'DELETE'
                    })
                    .then(response => {
                        if (response.ok) {
                            Swal.fire('Eliminado', 'El equipo ha sido eliminado.', 'success');
                            cargarEquipos();
                        } else {
                            Swal.fire('Error', 'Hubo un problema al eliminar el equipo.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error al eliminar equipo:', error);
                        Swal.fire('Error', 'Hubo un problema al eliminar el equipo.', 'error');
                    });
                }
            });
        }

        // Función para exportar la tabla a Excel
        function exportarExcel() {
            const equiposList = document.getElementById('equiposList');
            const equiposData = [];
            const rows = equiposList.querySelectorAll('tr');

            rows.forEach(row => {
                const cols = row.querySelectorAll('td');
                const rowData = [];
                cols.forEach(col => rowData.push(col.innerText));
                equiposData.push(rowData);
            });

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet([["ID", "Nombre", "Marca", "Acciones"], ...equiposData]);
            XLSX.utils.book_append_sheet(wb, ws, "Equipos");
            XLSX.writeFile(wb, "Equipos.xlsx");
        }
    </script>
</body>
</html>

