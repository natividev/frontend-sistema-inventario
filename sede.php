<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Sedes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #00365E;
            margin-bottom: 20px;
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
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            display: inline-block;
            background-color: #00365E;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
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
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #E7E9EB;
            color: #00365E;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .actions i {
            color: #00365E;
            margin-right: 10px;
            cursor: pointer;
            transition: color 0.3s;
        }

        .actions i:hover {
            color: #007bff;
        }

        .actions i[aria-label="Editar"]:hover {
            color: #28a745;
        }

        .actions i[aria-label="Eliminar"]:hover {
            color: #dc3545;
        }

        .search-container {
            margin-bottom: 20px;
            text-align: right;
        }

        .search-container input {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 300px;
        }

        .export-buttons {
            margin-bottom: 20px;
            text-align: right;
        }

        .export-buttons button {
            margin-left: 10px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Gestión de Sedes</h1>
        <form id="sedeForm">
            <div class="form-group">
                <label for="nombre">Nombre de la Sede</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <button type="submit">Añadir Sede</button>
        </form>

        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Buscar sede...">
        </div>

        <div class="export-buttons">
            <button onclick="generarExcel()">Generar Excel</button>
        </div>

        <h2>Lista de Sedes</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="sedesList">
                <!-- Aquí se agregarán las sedes dinámicamente -->
            </tbody>
        </table>
    </div>

    <script>
        const API_URL = 'http://31.220.97.169:5000/api/sede'; 
        let editingSedeId = null; 

        document.addEventListener('DOMContentLoaded', () => {
            cargarSedes();  

            document.getElementById('sedeForm').addEventListener('submit', function (event) {
                event.preventDefault();
                const nombre = document.getElementById('nombre').value.trim();

                if (nombre) {
                    if (editingSedeId) {
                        actualizarSede(editingSedeId, nombre);
                    } else {
                        agregarSede(nombre);
                    }
                } else {
                    Swal.fire('Error', 'El nombre de la sede no puede estar vacío.', 'error');
                }
            });

            document.getElementById('searchInput').addEventListener('input', function () {
                const query = this.value.toLowerCase();
                filtrarSedes(query);
            });
        });

        function cargarSedes() {
            fetch(API_URL)
                .then(response => response.json())
                .then(data => {
                    const sedesList = document.getElementById('sedesList');
                    sedesList.innerHTML = '';

                    data.forEach(sede => {
                        let row = `
                            <tr data-nombre="${sede.nombre.toLowerCase()}">
                                <td>${sede.id}</td>
                                <td>${sede.nombre}</td>
                                <td class="actions">
                                    <i class="fas fa-edit" aria-label="Editar" title="Editar" onclick="prepararEdicion(${sede.id}, '${sede.nombre}')"></i>
                                    <i class="fas fa-trash-alt" aria-label="Eliminar" title="Eliminar" onclick="eliminarSede(${sede.id})"></i>
                                </td>
                            </tr>`;
                        sedesList.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error al cargar sedes:', error);
                    Swal.fire('Error', 'Hubo un problema al cargar las sedes.', 'error');
                });
        }

        function agregarSede(nombre) {
            fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre })
            })
            .then(response => response.json())
            .then(() => {
                Swal.fire('Éxito', 'Sede añadida correctamente', 'success');
                cargarSedes();
                document.getElementById('sedeForm').reset();
            })
            .catch(error => {
                console.error('Error al añadir sede:', error);
                Swal.fire('Error', 'Hubo un problema al añadir la sede.', 'error');
            });
        }

        function eliminarSede(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarla',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`${API_URL}/${id}`, {
                        method: 'DELETE',
                    })
                    .then(response => response.json())
                    .then(() => {
                        Swal.fire('Eliminada', 'La sede ha sido eliminada.', 'success');
                        cargarSedes(); 
                    })
                    .catch(error => {
                        console.error('Error al eliminar sede:', error);
                        Swal.fire('Error', 'Hubo un problema al eliminar la sede.', 'error');
                    });
                }
            });
        }

        function prepararEdicion(id, nombre) {
            document.getElementById('nombre').value = nombre;
            editingSedeId = id; 
            document.querySelector('button[type="submit"]').textContent = 'Actualizar Sede'; 
        }

        function actualizarSede(id, nombre) {
            fetch(`${API_URL}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre })
            })
            .then(response => response.json())
            .then(() => {
                Swal.fire('Éxito', 'Sede actualizada correctamente', 'success');
                cargarSedes(); 
                document.getElementById('sedeForm').reset();
                editingSedeId = null; 
                document.querySelector('button[type="submit"]').textContent = 'Añadir Sede'; 
            })
            .catch(error => {
                console.error('Error al actualizar sede:', error);
                Swal.fire('Error', 'Hubo un problema al actualizar la sede.', 'error');
            });
        }

        function filtrarSedes(query) {
            const rows = document.querySelectorAll('#sedesList tr');
            rows.forEach(row => {
                const nombre = row.getAttribute('data-nombre');
                if (nombre.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function generarExcel() {
            const workbook = XLSX.utils.book_new();
            const worksheetData = [['ID', 'Nombre']]; 
            const rows = document.querySelectorAll('#sedesList tr');

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowData = Array.from(cells).map(cell => cell.textContent.trim());
                worksheetData.push(rowData);
            });

            const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Sedes');
            XLSX.writeFile(workbook, 'sedes.xlsx');
        }
    </script>
</body>
</html>

