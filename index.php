<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de gestion UNAB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        /* Tu estilo CSS existente */


        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .sidebar {
            width: 300px;
            background-color: #00365E;
            height: 100vh;
            padding-top: 20px;
            position: fixed;
            transition: 0.3s ease;
            overflow: hidden;
        }

        .sidebar.minimized {
            width: 80px;
        }

        .sidebar h2 {
            color: white;
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s ease;
            cursor: pointer;
        }

        .sidebar.minimized h2 span {
            display: none;
        }

        .sidebar a {
            padding: 15px 25px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: flex;
            align-items: center;
            transition: 0.3s ease;
        }

        .sidebar a span {
            margin-left: 15px;
            transition: 0.3s ease;
        }

        .sidebar.minimized a span {
            display: none;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        .dropdown-btn {
            background-color: #00365E;
            color: white;
            padding: 16px;
            border: none;
            outline: none;
            cursor: pointer;
            width: 100%;
            text-align: left;
            font-size: 18px;
            display: flex;
            align-items: center;
            transition: 0.3s;
        }

        .dropdown-btn i {
            margin-right: 10px;
        }

        .sidebar.minimized .dropdown-btn span {
            display: none;
        }

        .dropdown-btn:hover {
            background-color: #575757;
        }

        .dropdown-container {
            display: none;
            background-color: #00365E;
            padding-left: 15px;
        }

        .dropdown-container a {
            padding: 10px 20px;
            font-size: 16px;
            color: white;
        }

        .dropdown-container a:hover {
            background-color: #575757;
        }

        .fixed-modules {
            margin-top: 0.4px;
        }

        .fixed-modules a {
            padding: 15px 25px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: flex;
            align-items: center;
            transition: 0.3s ease;
        }

        .fixed-modules a:hover {
            background-color: #575757;
        }

        .fixed-modules a span {
            margin-left: 20px;
        }

        .sidebar.minimized .fixed-modules a span {
            display: none;
        }

        .content {
    margin-left: 300px;
    padding: 20px;
    width: calc(100% - 300px); /* Ajusta el ancho para el contenido */
    transition: 0.3s ease;
}

       .sidebar.minimized ~ .content {
    margin-left: 80px;
    width: calc(100% - 80px); /* Ajusta el ancho para cuando la barra lateral esté minimizada */
}

        .chart-container {
            display: block; /* Cambia a block para mostrarlo como predeterminado */
            margin-top: 20px;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .toggle-btn {
            position: absolute;
            top: 20px;
            left: 300px;
            background-color: transparent;
            color: black;
            font-size: 24px;
            padding: 10px;
            border: none;
            cursor: pointer;
            transition: 0.3s ease;
            z-index: 1000;
        }

        .sidebar.minimized + .toggle-btn {
            left: 80px;
        }

        .toggle-btn i {
            color: black;
        }

        .iframe-container {
           display: none; /* Ocultar iframe por defecto */
             width: 100%;
             height: calc(100vh - 20px); /* Ajusta la altura del iframe para que ocupe la pantalla menos 20px (puedes modificarlo) */
             margin: 0 auto;
}

    </style>
</head>
<body>

    <div class="sidebar">
        <h2 onclick="showChart()"><i class="fa-solid fa-house"></i><span>Dashboard</span></h2>
        
        <button class="dropdown-btn" onclick="toggleDropdown(this)"><i class="fa-solid fa-briefcase"></i><span> Administrador Regional </span>
            &#x25BC;
        </button>
        <div class="dropdown-container">
            <a href="Sede.php" target="iframe_content" onclick="navigateToModule(this);"><i class="fa-solid fa-route"></i><span> Sede</span></a>
            <a href="edificio.php" target="iframe_content" onclick="navigateToModule(this);"><i class="fa-solid fa-building"></i><span> Edificio</span></a>
            <a href="nivel.php" target="iframe_content" onclick="navigateToModule(this);"><i class="fas fa-layer-group"></i><span> Nivel</span></a>
            <a href="area.php" target="iframe_content" onclick="navigateToModule(this);"><i class="fas fa-map-marker-alt"></i><span> Area</span></a>
        </div>

        <button class="dropdown-btn" onclick="toggleDropdown(this)"><i class="fa-regular fa-clipboard"></i><span> Administracion Equipo</span>
            &#x25BC;
        </button>
        <div class="dropdown-container">
            <a href="equipo.php" target="iframe_content" onclick="navigateToModule(this);"><i class="fas fa-laptop"></i><span> Equipo</span></a>
            <a href="marca.php" target="iframe_content" onclick="navigateToModule(this);"><i class="fas fa-tags"></i><span> Marca</span></a>
            <a href="modelo.php" target="iframe_content" onclick="navigateToModule(this);"><i class="fas fa-cogs"></i><span> Modelo</span></a>
            <a href="especificacion.php" target="iframe_content" onclick="navigateToModule(this);"><i class="fas fa-file-alt"></i><span> Especificaciones</span></a>
        </div>

        <div class="fixed-modules">
            <a href="Inventario.php" target="iframe_content" onclick="navigateToModule(this);"><i class="fa-solid fa-boxes-stacked"></i><span> Inventario</span></a>
            <a href="empleado.php" target="iframe_content" onclick="navigateToModule(this);"><i class="fa-solid fa-user-gear"></i><span>Empleados</span></a>
        </div>
    </div>

    <button class="toggle-btn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>

    <div class="content">
        <!-- Contenedor para el gráfico -->
        <div class="chart-container" id="chartContainer">
            <canvas id="myChart"></canvas>
        </div>
       <button id="generatePdfButton" style="display: none;" onclick="generatePDF()">Generar PDF</button>



        <!-- Contenedor para la nueva gráfica de pastel -->
<div class="chart-container" id="chartContainerPie">
    <canvas id="myPieChart"></canvas>
</div>


 

        <!-- Contenedor para el iframe -->
        <div class="iframe-container" id="iframeContainer">
            <iframe name="iframe_content" width="100%" height="600px" style="border:none;"></iframe>
        </div>
    </div>

    

    <script>
        // Muestra el gráfico al cargar la página
        window.onload = function() {
            showChart();
        };

        function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('minimized');
           const chartContainer = document.getElementById('chartContainer');
    const iframeContainer = document.getElementById('iframeContainer');

    // Verificar si el iframe está visible antes de minimizar
    const isIframeVisible = iframeContainer.style.display === 'block';

    if (isIframeVisible) {
        // Si el iframe está visible, mantenerlo visible y ocultar el gráfico
        iframeContainer.style.display = 'block';
        chartContainer.style.display = 'none';
    } else {
        // Si no hay iframe visible, mostrar el gráfico
        chartContainer.style.display = 'block';
        iframeContainer.style.display = 'none';
    }
}

        function showChart() {
    const chartContainer = document.getElementById('chartContainer');
    const chartContainerPie = document.getElementById('chartContainerPie');
    const generatePdfButton = document.getElementById('generatePdfButton');
    chartContainer.style.display = 'block'; // Mostrar gráfico de barras
    chartContainerPie.style.display = 'block'; // Mostrar gráfico de pastel
    generatePdfButton.style.display = 'inline-block'; // Mostrar botón Generar PDF
    const iframeContainer = document.getElementById('iframeContainer');
    iframeContainer.style.display = 'none'; // Ocultar iframe

    // Gráfico de barras existente
   const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Sede', 'Edificio', 'Nivel', 'Área'], // Etiquetas de los módulos
            datasets: [{
                label: 'Estadísticas de Módulos',
                data: [20, 15, 10, 25], // Datos de ejemplo
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Nueva gráfica de pastel
    const ctxPie = document.getElementById('myPieChart').getContext('2d');
    const myPieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Equipo', 'Marca', 'Modelo', 'Especificaciones'], // Etiquetas de los módulos
            datasets: [{
                label: 'Estadísticas de Módulos de Equipo',
                data: [30, 20, 15, 35], // Datos de ejemplo para los módulos
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}
   

        function navigateToModule(link) {
    // Ocultar las gráficas de barras y de pastel
    const chartContainer = document.getElementById('chartContainer');
    const chartContainerPie = document.getElementById('chartContainerPie');
    chartContainer.style.display = 'none'; // Ocultar gráfico de barras
    chartContainerPie.style.display = 'none'; // Ocultar gráfico de pastel

      const generatePdfButton = document.getElementById('generatePdfButton');
    generatePdfButton.style.display = 'none'; // Ocultar botón Generar PDF

    // Mostrar el iframe
    const iframeContainer = document.getElementById('iframeContainer');
    iframeContainer.style.display = 'block'; // Mostrar iframe

    closeAllDropdowns(); // Cerrar todos los menús desplegables
    iframeContainer.style.width = '100%';
    iframeContainer.style.height = '100%'; // Ocupa todo el alto disponible
}

        function toggleDropdown(button) {
            const dropdownContainer = button.nextElementSibling;
            dropdownContainer.style.display = dropdownContainer.style.display === 'block' ? 'none' : 'block';
        }

        function closeAllDropdowns() {
            const dropdowns = document.querySelectorAll('.dropdown-container');
            dropdowns.forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        }

        function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Títulos y datos de la gráfica de barras
    doc.text("Estadísticas de Módulos", 10, 10);
    doc.text("Sede: 20", 10, 20);
    doc.text("Edificio: 15", 10, 30);
    doc.text("Nivel: 10", 10, 40);
    doc.text("Área: 25", 10, 50);
    
    // Títulos y datos de la gráfica de pastel
    doc.text("Estadísticas de Módulos de Equipo", 10, 70);
    doc.text("Equipo: 30", 10, 80);
    doc.text("Marca: 20", 10, 90);
    doc.text("Modelo: 15", 10, 100);
    doc.text("Especificaciones: 35", 10, 110);

    // Guardar el PDF
    doc.save("estadisticas_modulos.pdf");
}
    </script>
</body>
</html>

