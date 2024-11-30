<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Edificios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script> <!-- Librería para exportar a Excel -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
             margin: 0;
            padding: 0;
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
             margin-bottom: 20px;
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
            font-size: 16px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .button-group button {
            flex: 1;
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

        .search-group {
            margin-bottom: 20px;
        }

        .search-group input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Edificios</h1>
        
        <!-- Buscador -->
        <div class="search-group">
            <label for="searchInput">Buscar Edificio</label>
            <input type="text" id="searchInput" placeholder="Buscar por nombre o sede...">
        </div>

        <form id="edificioForm">
            <div class="form-group">
                <label for="nombre">Nombre del Edificio</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="sedeId">Seleccionar Sede</label>
                <select id="sedeId" name="sedeId" required>
                    <option value="">Seleccionar sede...</option>
                    <!-- Aquí se llenará la lista de sedes -->
                </select>
            </div>

            <div class="button-group">
                <button type="submit">Añadir Edificio</button>
                <button id="exportExcel" type="button">Generar Excel</button>
            </div>
        </form>

        <h2>Lista de Edificios</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Sede</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="edificiosList">
                <!-- Aquí se agregarán los edificios dinámicamente -->
            </tbody>
        </table>
    </div>

    <script>
        const API_URL_EDIFICIO = 'http://31.220.97.169:5000/api/edificio';
        const API_URL_SEDE = 'http://31.220.97.169:5000/api/sede';
        let edificiosData = []; // Variable para almacenar los edificios cargados

        document.addEventListener('DOMContentLoaded', () => {
            cargarSedes();
            cargarEdificios();

            document.getElementById('edificioForm').addEventListener('submit', function (event) {
                event.preventDefault();
                const nombre = document.getElementById('nombre').value;
                const sedeId = document.getElementById('sedeId').value;
                if (this.dataset.editing) {
                    actualizarEdificio(this.dataset.editing);
                } else {
                    agregarEdificio(nombre, sedeId);
                }
            });

            // Función para generar el archivo Excel
            document.getElementById('exportExcel').addEventListener('click', function() {
                // Obtener los datos de la tabla
                const table = document.querySelector('table');
                const workbook = XLSX.utils.table_to_book(table, {sheet: "Edificios"});

                // Exportar el archivo Excel
                XLSX.writeFile(workbook, 'edificios.xlsx');
            });

            // Buscador
            document.getElementById('searchInput').addEventListener('input', function() {
                const searchQuery = this.value.toLowerCase();
                const filteredEdificios = edificiosData.filter(edificio => {
                    return (
                        edificio.nombre.toLowerCase().includes(searchQuery) ||
                        (edificio.sede && edificio.sede.nombre.toLowerCase().includes(searchQuery))
                    );
                });
                mostrarEdificios(filteredEdificios);
            });
        });

        function cargarSedes() {
            fetch(API_URL_SEDE)
                .then(response => response.json())
                .then(data => {
                    const sedeSelect = document.getElementById('sedeId');
                    sedeSelect.innerHTML = '<option value="">Seleccionar sede...</option>';

                    data.forEach(sede => {
                        const option = `<option value="${sede.id}">${sede.nombre}</option>`;
                        sedeSelect.innerHTML += option;
                    });
                })
                .catch(error => {
                    console.error('Error al cargar sedes:', error);
                });
        }

        function cargarEdificios() {
            fetch(API_URL_EDIFICIO)
                .then(response => response.json())
                .then(data => {
                    edificiosData = data; // Guardar los datos de edificios
                    mostrarEdificios(data);
                })
                .catch(error => {
                    console.error('Error al cargar edificios:', error);
                });
        }

        function mostrarEdificios(edificios) {
            const edificiosList = document.getElementById('edificiosList');
            edificiosList.innerHTML = '';

            edificios.forEach(edificio => {
                const row = `
                    <tr>
                        <td>${edificio.id}</td>
                        <td>${edificio.nombre}</td>
                        <td>${edificio.sede ? edificio.sede.nombre : 'N/A'}</td>
                        <td class="actions">
                            <i class="fas fa-edit" title="Editar" onclick="prepararEdificio(${edificio.id}, '${edificio.nombre}', ${edificio.sede ? edificio.sede.id : 'N/A'})"></i>
                            <i class="fas fa-trash-alt" title="Eliminar" onclick="eliminarEdificio(${edificio.id})"></i>
                        </td>
                    </tr>`;
                edificiosList.innerHTML += row;
            });
        }

        function agregarEdificio(nombre, sedeId) {
            fetch(API_URL_EDIFICIO, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre, sedeId: sedeId })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire('Éxito', 'Edificio añadido correctamente', 'success');
                cargarEdificios();
                document.getElementById('edificioForm').reset();
            })
            .catch(error => {
                console.error('Error al añadir edificio:', error);
                Swal.fire('Error', 'Hubo un problema al añadir el edificio.', 'error');
            });
        }

        function eliminarEdificio(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${API_URL_EDIFICIO}/${id}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire('Éxito', 'Edificio eliminado correctamente', 'success');
                        cargarEdificios();
                    })
                    .catch(error => {
                        console.error('Error al eliminar edificio:', error);
                        Swal.fire('Error', 'Hubo un problema al eliminar el edificio.', 'error');
                    });
                }
            });
        }

        function prepararEdificio(id, nombre, sedeId) {
            document.getElementById('nombre').value = nombre;
            document.getElementById('sedeId').value = sedeId;
            const form = document.getElementById('edificioForm');
            form.dataset.editing = id;
        }

        function actualizarEdificio(id) {
            const nombre = document.getElementById('nombre').value;
            const sedeId = document.getElementById('sedeId').value;

            fetch(`${API_URL_EDIFICIO}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre, sedeId: sedeId })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire('Éxito', 'Edificio actualizado correctamente', 'success');
                cargarEdificios();
                document.getElementById('edificioForm').reset();
                delete document.getElementById('edificioForm').dataset.editing;
            })
            .catch(error => {
                console.error('Error al actualizar edificio:', error);
                Swal.fire('Error', 'Hubo un problema al actualizar el edificio.', 'error');
            });
        }
    </script>
</body>
</html>
