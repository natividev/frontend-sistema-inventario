<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Marcas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
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

        .button-container {
            display: flex; /* Usar flexbox para alinear los botones */
            justify-content: space-between; /* Espacio entre los botones */
            margin-top: 10px; /* Espaciado para el contenedor de botones */
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
            margin-bottom: 20px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Marcas</h1>

        <!-- Buscador -->
        <input type="text" id="search" placeholder="Buscar marca..." onkeyup="filtrarMarcas()" />

        <form id="marcaForm">
            <div class="form-group">
                <label for="nombre">Nombre de la Marca</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="button-container">
                <button type="submit">Añadir Marca</button>
                <button id="exportExcel" type="button">Generar Excel</button> <!-- Botón para generar Excel -->
            </div>
        </form>

        <h2>Lista de Marcas</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="marcasList">
                <!-- Aquí se agregarán las marcas dinámicamente -->
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const API_URL = 'http://31.220.97.169:5000/api/marca'; // Asegúrate de que este sea el correcto
        let editingMarcaId = null; // Variable para almacenar el ID de la marca que se está editando

        document.addEventListener('DOMContentLoaded', () => {
            cargarMarcas();  // Cargar marcas al cargar la página

            document.getElementById('marcaForm').addEventListener('submit', function (event) {
                event.preventDefault();
                const nombre = document.getElementById('nombre').value;

                if (editingMarcaId) {
                    actualizarMarca(editingMarcaId, nombre);
                } else {
                    agregarMarca(nombre);
                }
            });

            // Agregar evento para el botón de exportar Excel
            document.getElementById('exportExcel').addEventListener('click', generarExcel);
        });

        // Función para cargar marcas desde la API
        function cargarMarcas() {
            fetch(API_URL)
                .then(response => response.json())
                .then(data => {
                    let marcasList = document.getElementById('marcasList');
                    marcasList.innerHTML = '';

                    data.forEach(marca => {
                        let row = `
                            <tr>
                                <td>${marca.id}</td>
                                <td>${marca.nombre}</td>
                                <td class="actions">
                                    <i class="fas fa-edit" title="Editar" onclick="prepararEdicion(${marca.id}, '${marca.nombre}')"></i>
                                    <i class="fas fa-trash-alt" title="Eliminar" onclick="eliminarMarca(${marca.id})"></i>
                                </td>
                            </tr>`;
                        marcasList.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error al cargar marcas:', error);
                });
        }

        // Función para agregar una marca
        function agregarMarca(nombre) {
            fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al añadir la marca');
                }
                return response.json();
            })
            .then(data => {
                Swal.fire('Éxito', 'Marca añadida correctamente', 'success');
                cargarMarcas();
                document.getElementById('marcaForm').reset();
            })
            .catch(error => {
                console.error('Error al añadir marca:', error);
                Swal.fire('Error', 'Hubo un problema al añadir la marca.', 'error');
            });
        }

        // Función para eliminar una marca usando SweetAlert
        function eliminarMarca(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarla',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${API_URL}/${id}`, {
                        method: 'DELETE',
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error al eliminar la marca. Código de estado: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        Swal.fire('Eliminada', 'La marca ha sido eliminada.', 'success');
                        cargarMarcas(); // Actualizar la lista de marcas
                    })
                    .catch(error => {
                        console.error('Error al eliminar marca:', error);
                        Swal.fire('Error', 'Hubo un problema al eliminar la marca: ' + error.message, 'error');
                    });
                }
            });
        }

        // Función para preparar la edición de una marca
        function prepararEdicion(id, nombre) {
            document.getElementById('nombre').value = nombre;
            editingMarcaId = id; // Guardar el ID de la marca en edición
            document.querySelector('button[type="submit"]').textContent = 'Actualizar Marca'; // Cambiar el texto del botón
        }

        // Función para actualizar una marca usando PATCH
        function actualizarMarca(id, nombre) {
            fetch(`${API_URL}/${id}`, {
                method: 'PATCH',  // Cambiado a PATCH
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar la marca');
                }
                return response.json();
            })
            .then(data => {
                Swal.fire('Éxito', 'Marca actualizada correctamente', 'success');
                cargarMarcas();
                document.getElementById('marcaForm').reset();
                editingMarcaId = null; // Reiniciar la variable
                document.querySelector('button[type="submit"]').textContent = 'Añadir Marca'; // Restablecer el texto del botón
            })
            .catch(error => {
                console.error('Error al actualizar marca:', error);
                Swal.fire('Error', 'Hubo un problema al actualizar la marca.', 'error');
            });
        }

        // Función para filtrar marcas con el buscador
        function filtrarMarcas() {
            const searchInput = document.getElementById('search').value.toLowerCase();
            const rows = document.querySelectorAll('#marcasList tr');
            rows.forEach(row => {
                const nombre = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                if (nombre.includes(searchInput)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Función para generar el archivo Excel
        function generarExcel() {
            fetch(API_URL)
                .then(response => response.json())
                .then(data => {
                    const ws = XLSX.utils.json_to_sheet(data);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, "Marcas");
                    XLSX.writeFile(wb, 'marcas.xlsx');
                })
                .catch(error => {
                    console.error('Error al generar el archivo Excel:', error);
                    Swal.fire('Error', 'Hubo un problema al generar el archivo Excel.', 'error');
                });
        }
    </script>
</body>
</html>

