<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Modelos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>
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

        #search {
            padding: 8px;
            margin: 10px 0;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Modelos</h1>
        <form id="modeloForm">
            <div class="form-group">
                <label for="nombre">Nombre del Modelo</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="marcaId">Seleccionar Marca</label>
                <select id="marcaId" name="marcaId" required>
                    <option value="">Seleccionar marca...</option>
                </select>
            </div>
            <button type="submit">Añadir Modelo</button>
        </form>

        <!-- Buscador -->
        <input type="text" id="search" placeholder="Buscar modelos por nombre..." oninput="filtrarModelos()">

        <button id="generarExcel">Generar Excel</button>

        <h2>Lista de Modelos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Marca</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="modelosList">
                <tr>
                    <td colspan="4">Cargando modelos...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const API_URL_MODELO = 'http://31.220.97.169:5000/api/modelo';
        const API_URL_MARCA = 'http://31.220.97.169:5000/api/marca';

        document.addEventListener('DOMContentLoaded', () => {
            cargarMarcas();
            cargarModelo();

            document.getElementById('modeloForm').addEventListener('submit', function (event) {
                event.preventDefault();
                const nombre = document.getElementById('nombre').value;
                const marcaId = document.getElementById('marcaId').value;

                if (this.dataset.editing) {
                    actualizarModelo(this.dataset.editing);
                } else {
                    agregarModelo(nombre, marcaId);
                }
            });
        });

        function cargarMarcas() {
            fetch(API_URL_MARCA)
                .then(response => response.json())
                .then(data => {
                    const marcaSelect = document.getElementById('marcaId');
                    marcaSelect.innerHTML = '<option value="">Seleccionar marca...</option>';
                    data.forEach(marca => {
                        const option = `<option value="${marca.id}">${marca.nombre}</option>`;
                        marcaSelect.innerHTML += option;
                    });
                })
                .catch(error => {
                    console.error('Error al cargar marcas:', error);
                });
        }

        function cargarModelo() {
            fetch(API_URL_MODELO)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta de la API');
                    }
                    return response.json();
                })
                .then(data => {
                    const modelosList = document.getElementById('modelosList');
                    modelosList.innerHTML = '';

                    if (data.length === 0) {
                        modelosList.innerHTML = '<tr><td colspan="4">No hay modelos registrados</td></tr>';
                        return;
                    }

                    data.forEach(modelo => {
                        const row = `
                            <tr>
                                <td>${modelo.id}</td>
                                <td>${modelo.nombre}</td>
                                <td>${modelo.marca ? modelo.marca.nombre : 'Sin Marca'}</td>
                                <td class="actions">
                                    <i class="fas fa-edit" title="Editar" onclick="prepararModelo(${modelo.id}, '${modelo.nombre}', ${modelo.marca ? modelo.marca.id : 'null'})"></i>
                                    <i class="fas fa-trash-alt" title="Eliminar" onclick="eliminarModelo(${modelo.id})"></i>
                                </td>
                            </tr>`;
                        modelosList.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error al cargar modelos:', error);
                    const modelosList = document.getElementById('modelosList');
                    modelosList.innerHTML = '<tr><td colspan="4">Error al cargar modelos</td></tr>';
                });
        }

        function agregarModelo(nombre, marcaId) {
            fetch(API_URL_MODELO, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre, idMarca: Number(marcaId) })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al agregar el modelo');
                }
                return response.json();
            })
            .then(data => {
                Swal.fire('Añadido', 'Modelo agregado correctamente', 'success');
                cargarModelo();
                document.getElementById('modeloForm').reset();
            })
            .catch(error => {
                console.error('Error al agregar modelo:', error);
                Swal.fire('Error', error.message || 'Hubo un problema al agregar el modelo.', 'error');
            });
        }

        function eliminarModelo(id) {
            fetch(`${API_URL_MODELO}/${id}`, {
                method: 'DELETE',
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al eliminar el modelo');
                }
                Swal.fire('Eliminado', 'Modelo eliminado correctamente', 'success');
                cargarModelo();
            })
            .catch(error => {
                console.error('Error al eliminar modelo:', error);
                Swal.fire('Error', error.message || 'Hubo un problema al eliminar el modelo.', 'error');
            });
        }

        function prepararModelo(id, nombre, marcaId) {
            document.getElementById('nombre').value = nombre;
            document.getElementById('marcaId').value = marcaId;
            document.getElementById('modeloForm').dataset.editing = id;
        }

        function actualizarModelo(id) {
            const nombre = document.getElementById('nombre').value;
            const marcaId = document.getElementById('marcaId').value;

            fetch(`${API_URL_MODELO}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre, idMarca: Number(marcaId) })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar el modelo');
                }
                return response.json();
            })
            .then(data => {
                Swal.fire('Actualizado', 'Modelo actualizado correctamente', 'success');
                cargarModelo();
                document.getElementById('modeloForm').reset();
                delete document.getElementById('modeloForm').dataset.editing;
            })
            .catch(error => {
                console.error('Error al actualizar modelo:', error);
                Swal.fire('Error', error.message || 'Hubo un problema al actualizar el modelo.', 'error');
            });
        }

        function filtrarModelos() {
            const searchInput = document.getElementById('search').value.toLowerCase();
            const rows = document.querySelectorAll('#modelosList tr');
            
            rows.forEach(row => {
                const nombreCell = row.cells[1];
                if (nombreCell) {
                    const nombre = nombreCell.textContent.toLowerCase();
                    row.style.display = nombre.includes(searchInput) ? '' : 'none';
                }
            });
        }
    </script>
</body>
</html>
