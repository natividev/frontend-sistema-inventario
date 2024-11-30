<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Áreas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script> <!-- SheetJS -->
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
            margin-top: 20px;
            background-color: #00365E; /* Mismo color que el botón de añadir */
        }

        .btn-export:hover {
            background-color: #004f8d; /* Mismo hover que el botón de añadir */
        }

        /* Estilos para el buscador */
        .search-container {
            margin-bottom: 20px;
        }

        .search-container input {
            padding: 8px;
            width: 100%;
            max-width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Áreas</h1>
        <form id="areaForm">
            <div class="form-group">
                <label for="nombre">Nombre del Área</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="nivelId">Seleccionar Nivel</label>
                <select id="nivelId" name="nivelId" required>
                    <option value="">Seleccionar nivel...</option>
                    <!-- Aquí se llenará la lista de niveles -->
                </select>
            </div>
            <button type="submit">Añadir Área</button>
        </form>

        <!-- Buscador -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Buscar por nombre..." onkeyup="filtrarAreas()">
        </div>

        <h2>Lista de Áreas</h2>
        <button class="btn-export" onclick="exportarExcel()">Generar Excel</button> <!-- Botón para generar Excel -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Nivel</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="areasList">
                <!-- Aquí se agregarán las áreas dinámicamente -->
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const API_URL_AREA = 'http://31.220.97.169:5000/api/area'; // URL de la API para áreas
        const API_URL_NIVEL = 'http://31.220.97.169:5000/api/nivel'; // URL para obtener los niveles

        document.addEventListener('DOMContentLoaded', () => {
            cargarNiveles();  // Cargar niveles al cargar la página
            cargarAreas();    // Cargar áreas al cargar la página

            document.getElementById('areaForm').addEventListener('submit', function (event) {
                event.preventDefault();
                const nombre = document.getElementById('nombre').value;
                const nivelId = document.getElementById('nivelId').value;
                if (this.dataset.editing) {
                    actualizarArea(this.dataset.editing);
                } else {
                    agregarArea(nombre, nivelId);
                }
            });
        });

        // Función para cargar los niveles desde la API
        function cargarNiveles() {
            fetch(API_URL_NIVEL)
                .then(response => response.json())
                .then(data => {
                    const nivelSelect = document.getElementById('nivelId');
                    nivelSelect.innerHTML = '<option value="">Seleccionar nivel...</option>'; // Resetea el dropdown

                    data.forEach(nivel => {
                        const option = `<option value="${nivel.id}">${nivel.nombre}</option>`;
                        nivelSelect.innerHTML += option; // Agrega cada nivel al dropdown
                    });
                })
                .catch(error => {
                    console.error('Error al cargar niveles:', error);
                });
        }

        // Función para cargar áreas desde la API
        function cargarAreas() {
            fetch(API_URL_AREA)
                .then(response => response.json())
                .then(data => {
                    const areasList = document.getElementById('areasList');
                    areasList.innerHTML = '';

                    data.forEach(area => {
                        const row = `
                            <tr>
                                <td>${area.id}</td>
                                <td>${area.nombre}</td>
                                <td>${area.nivel.nombre}</td> <!-- Mostrar el nombre del nivel -->
                                <td class="actions">
                                    <i class="fas fa-edit" title="Editar" onclick="prepararArea(${area.id}, '${area.nombre}', ${area.nivel.id})"></i>
                                    <i class="fas fa-trash-alt" title="Eliminar" onclick="eliminarArea(${area.id})"></i>
                                </td>
                            </tr>`;
                        areasList.innerHTML += row;
                    });

                    reiniciarIDs(); // Reiniciar IDs después de cargar áreas
                })
                .catch(error => {
                    console.error('Error al cargar áreas:', error);
                });
        }

        // Función para filtrar áreas en función de la búsqueda
        function filtrarAreas() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#areasList tr');

            rows.forEach(row => {
                const nombre = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                if (nombre.includes(searchInput)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Función para reiniciar IDs
        function reiniciarIDs() {
            const areasList = document.getElementById('areasList');
            const rows = areasList.querySelectorAll('tr');
            rows.forEach((row, index) => {
                const cell = row.querySelector('td:first-child');
                cell.textContent = index + 1; // Establecer el ID a base 1
            });
        }

        // Función para agregar un área
        function agregarArea(nombre, nivelId) {
            fetch(API_URL_AREA, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre, nivelId: nivelId }) // Enviar el nombre y el ID del nivel
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire('Éxito', 'Área añadida correctamente', 'success');
                cargarAreas();
                document.getElementById('areaForm').reset();
            })
            .catch(error => {
                console.error('Error al añadir área:', error);
                Swal.fire('Error', 'Hubo un problema al añadir el área.', 'error');
            });
        }

        // Función para eliminar un área usando SweetAlert
        function eliminarArea(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${API_URL_AREA}/${id}`, {
                        method: 'DELETE',
                    })
                    .then(response => response.json())
                    .then(() => {
                        Swal.fire('Eliminado', 'Área eliminada correctamente', 'success');
                        cargarAreas();
                    })
                    .catch(error => {
                        console.error('Error al eliminar área:', error);
                        Swal.fire('Error', 'Hubo un problema al eliminar el área.', 'error');
                    });
                }
            });
        }

        // Función para preparar el formulario para editar un área
        function prepararArea(id, nombre, nivelId) {
            document.getElementById('nombre').value = nombre;
            document.getElementById('nivelId').value = nivelId;
            const form = document.getElementById('areaForm');
            form.dataset.editing = id; // Establecer el ID del área a editar
        }

        // Función para actualizar un área
        function actualizarArea(id) {
            const nombre = document.getElementById('nombre').value;
            const nivelId = document.getElementById('nivelId').value;

            fetch(`${API_URL_AREA}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre, nivelId: nivelId })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire('Éxito', 'Área actualizada correctamente', 'success');
                cargarAreas();
                document.getElementById('areaForm').reset();
                delete document.getElementById('areaForm').dataset.editing;
            })
            .catch(error => {
                console.error('Error al actualizar área:', error);
                Swal.fire('Error', 'Hubo un problema al actualizar el área.', 'error');
            });
        }

        // Función para exportar a Excel
        function exportarExcel() {
            fetch(API_URL_AREA)
                .then(response => response.json())
                .then(data => {
                    const areas = data.map(area => ({
                        ID: area.id,
                        Nombre: area.nombre,
                        Nivel: area.nivel.nombre, // Nombre del nivel
                    }));

                    const ws = XLSX.utils.json_to_sheet(areas);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Áreas');
                    XLSX.writeFile(wb, 'areas.xlsx');
                })
                .catch(error => {
                    console.error('Error al exportar a Excel:', error);
                });
        }
    </script>
</body>
</html>
