<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Niveles</title>
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

        .btn-group {
            display: flex;
            gap: 10px;
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

        .search-container {
            margin-bottom: 20px;
        }

        .search-container input {
            width: 50%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Niveles</h1>
        <form id="nivelForm">
            <div class="form-group">
                <label for="nombre">Nombre del Nivel</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="btn-group">
                <button type="submit">Añadir Nivel</button>
                <button type="button" class="generate-excel" onclick="generarExcel()">Generar Excel</button>
            </div>
        </form>

        <!-- Buscador -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Buscar Nivel..." onkeyup="buscarNivel()">
        </div>

        <h2>Lista de Niveles</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="nivelesList">
                <!-- Aquí se agregarán los niveles dinámicamente -->
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        const API_URL = 'http://31.220.97.169:5000/api/nivel'; // Asegúrate de que este sea el correcto
        let nivelesData = []; // Para almacenar los niveles cargados

        document.addEventListener('DOMContentLoaded', () => {
            cargarNiveles();  // Cargar niveles al cargar la página

            document.getElementById('nivelForm').addEventListener('submit', function (event) {
                event.preventDefault();
                const nombre = document.getElementById('nombre').value;
                if (this.dataset.editing) {
                    actualizarNivel(this.dataset.editing);
                } else {
                    agregarNivel(nombre);
                }
            });
        });

        // Función para cargar niveles desde la API
        function cargarNiveles() {
            fetch(API_URL)
                .then(response => response.json())
                .then(data => {
                    nivelesData = data; // Guardar los datos cargados
                    mostrarNiveles(nivelesData); // Mostrar niveles
                })
                .catch(error => {
                    console.error('Error al cargar niveles:', error);
                    Swal.fire('Error', 'Hubo un problema al cargar los niveles.', 'error');
                });
        }

        // Función para mostrar los niveles en la tabla
        function mostrarNiveles(niveles) {
            let nivelesList = document.getElementById('nivelesList');
            nivelesList.innerHTML = '';

            // Renumerar IDs
            niveles.forEach((nivel, index) => {
                let newId = index + 1; // Nuevo ID basado en la posición
                let row = `
                    <tr>
                        <td>${newId}</td>
                        <td>${nivel.nombre}</td>
                        <td class="actions">
                            <i class="fas fa-edit" title="Editar" onclick="prepararEdicion(${nivel.id}, '${nivel.nombre}')"></i>
                            <i class="fas fa-trash-alt" title="Eliminar" onclick="eliminarNivel(${nivel.id})"></i>
                        </td>
                    </tr>`;
                nivelesList.innerHTML += row;
            });
        }

        // Función de búsqueda
        function buscarNivel() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const filteredNiveles = nivelesData.filter(nivel => nivel.nombre.toLowerCase().includes(searchInput));
            mostrarNiveles(filteredNiveles);
        }

        // Función para agregar un nivel
        function agregarNivel(nombre) {
            fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire('Éxito', 'Nivel añadido correctamente', 'success');
                cargarNiveles(); // Cargar niveles para actualizar la lista
                document.getElementById('nivelForm').reset();
            })
            .catch(error => {
                console.error('Error al añadir nivel:', error);
                Swal.fire('Error', 'Hubo un problema al añadir el nivel.', 'error');
            });
        }

        // Función para eliminar un nivel usando SweetAlert
        function eliminarNivel(id) {
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
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire('Eliminado', 'El nivel ha sido eliminado.', 'success');
                        cargarNiveles(); // Actualizar la lista de niveles
                    })
                    .catch(error => {
                        console.error('Error al eliminar nivel:', error);
                        Swal.fire('Error', 'Hubo un problema al eliminar el nivel.', 'error');
                    });
                }
            });
        }

        // Función para preparar la edición de un nivel
        function prepararEdicion(id, nombre) {
            document.getElementById('nombre').value = nombre;
            document.getElementById('nivelForm').dataset.editing = id; // Guardar ID en el formulario

            // Cambiar el botón de añadir a editar
            const button = document.querySelector('button[type="submit"]');
            button.textContent = 'Actualizar Nivel';
        }

        // Función para actualizar un nivel
        function actualizarNivel(id) {
            const nombre = document.getElementById('nombre').value;

            fetch(`${API_URL}/${id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ nombre: nombre })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire('Éxito', 'Nivel actualizado correctamente', 'success');
                cargarNiveles(); // Cargar niveles para actualizar la lista
                document.getElementById('nivelForm').reset();
                delete document.getElementById('nivelForm').dataset.editing; // Limpiar el ID
                const button = document.querySelector('button[type="submit"]');
                button.textContent = 'Añadir Nivel';
            })
            .catch(error => {
                console.error('Error al actualizar nivel:', error);
                Swal.fire('Error', 'Hubo un problema al actualizar el nivel.', 'error');
            });
        }

        // Función para generar el archivo Excel
        function generarExcel() {
            const niveles = nivelesData.map((nivel, index) => ({
                ID: index + 1,
                Nombre: nivel.nombre
            }));
            const ws = XLSX.utils.json_to_sheet(niveles);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Niveles");
            XLSX.writeFile(wb, "niveles.xlsx");
        }
    </script>
</body>
</html>
