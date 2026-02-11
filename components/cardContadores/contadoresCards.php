<style>
.card-glass {
    position: relative;
    overflow: hidden;
    background: transparent !important;
}

/* Capa 1: Imagen de fondo (detrás de todo) */
.card-glass::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: -2; /* La capa más profunda */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

/* Capa 2: El filtro de desenfoque (encima de la imagen) */
.card-glass::after {
    content: "";
    position: absolute;
    inset: 0;
    z-index: -1; /* Entre la imagen y el contenido */
    background-color: rgba(255, 255, 255, 0.1); /* Tinte blanco */
    backdrop-filter: blur(5px); /* Ajusta el blur como necesites */
    -webkit-backdrop-filter: blur(5px);
}

/* Capa 3: El contenido (encima de todo) */
.card-glass .card-body {
    position: relative;
    z-index: 1;
}

/* Específicos para cada card (se aplica a la capa de imagen) */
.card-glass.patron-1::before { background-image: url('img/patron_1.svg'); }
.card-glass.patron-2::before { background-image: url('img/patron_2.svg'); }
.card-glass.patron-3::before { background-image: url('img/patron_3.svg'); }
</style>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card card-glass patron-1 text-center border-0 shadow-lg h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <i class="bi bi-gift-fill" style="font-size: 2.5rem; color: #000;"></i>
                <h5 class="card-title mt-2" style="color: #000;">Entregas realizadas</h5>
                <h2 id="totalEntregas" class="fw-bold" style="color: #000;">0</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card card-glass patron-2 text-center border-0 shadow-lg h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <i class="bi bi-people-fill" style="font-size: 2.5rem; color: #000;"></i>
                <h5 class="card-title mt-2" style="color: #000;">Usuarios Registrados</h5>
                <h2 id="totalUsuarios" class="fw-bold" style="color: #000;">0</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card card-glass patron-3 text-center border-0 shadow-lg h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <i class="bi bi-calendar-event-fill" style="font-size: 2.5rem; color: #000;"></i>
                <h5 class="card-title mt-2" style="color: #000;">Entregas Este Mes</h5>
                <h2 id="entregasMes" class="fw-bold" style="color: #000;">0</h2>
            </div>
        </div>
    </div>
</div>

<!-- Fila para el gráfico reducido -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header text-center bg-orange-dark text-white mb-0">
                <h5>Entregas por Sede</h5>
            </div>
            <div class="card-body">
                <canvas id="contadoresChart" width="400" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header text-center bg-indigo-dark text-white mb-0">
                <h5>Entregas por Mes (últimos 12 meses)</h5>
            </div>
            <div class="card-body">
                <canvas id="lineChartEntregasMes" width="400" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let chart, lineChart;

    function actualizarContadores() {
        fetch('components/cardContadores/actualizarContadores.php')
            .then(response => response.json())
            .then(data => {
                console.log('Datos recibidos:', data); // Para debugging
                
                // Animación suave al actualizar contadores - SOLO LOS 3 QUE EXISTEN
                animateCounter('totalEntregas', data.totalEntregas);
                animateCounter('totalUsuarios', data.totalUsuarios);
                animateCounter('entregasMes', data.entregasMes);
                // ELIMINADA: animateCounter('usuariosActualizados', data.usuariosActualizados);

                // Actualizar gráficos
                actualizarGrafico(data);
                actualizarLineChart(data);
            })
            .catch(error => {
                console.error('Error al actualizar contadores:', error);
            });
    }

    function animateCounter(id, target) {
        const element = document.getElementById(id);
        if (!element) {
            console.warn(`Element with id '${id}' not found`);
            return;
        }
        
        const start = parseInt(element.textContent) || 0;
        const duration = 1000; // 1 segundo
        const step = (target - start) / (duration / 50);
        let current = start;
        const timer = setInterval(() => {
            current += step;
            if ((step > 0 && current >= target) || (step < 0 && current <= target)) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 50);
    }

    function actualizarGrafico(data) {
        const ctx = document.getElementById('contadoresChart').getContext('2d');
        
        if (chart) {
            chart.destroy(); // Destruir gráfico anterior para actualizar
        }

        // Verificar que tenemos datos para el gráfico
        if (!data.labels || !data.values || data.labels.length === 0) {
            console.warn('No hay datos para el gráfico de sedes');
            return;
        }

        chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels, // Usar labels de entregas por sede
                datasets: [{
                    label: 'Entregas por Sede',
                    data: data.values, // Usar values de entregas por sede
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.6)', // Indigo
                        'rgba(236, 72, 153, 0.6)', // Magenta
                        'rgba(20, 184, 166, 0.6)', // Teal
                        'rgba(249, 115, 22, 0.6)', // Orange
                        'rgba(34, 197, 94, 0.6)'   // Lime para extras
                    ],
                    borderColor: [
                        'rgba(99, 102, 241, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(20, 184, 166, 1)',
                        'rgba(249, 115, 22, 1)',
                        'rgba(34, 197, 94, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    function actualizarLineChart(data) {
        const ctx = document.getElementById('lineChartEntregasMes').getContext('2d');
        
        if (lineChart) lineChart.destroy();
        
        // Verificar que tenemos datos para el gráfico
        if (!data.labelsMeses || !data.valoresMeses || data.labelsMeses.length === 0) {
            console.warn('No hay datos para el gráfico de meses');
            return;
        }
        
        lineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labelsMeses, // meses
                datasets: [{
                    label: 'Entregas',
                    data: data.valoresMeses, // cantidades por mes
                    fill: false,
                    borderColor: 'rgba(20, 184, 166, 1)',
                    backgroundColor: 'rgba(20, 184, 166, 0.2)',
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(20, 184, 166, 1)',
                    pointBorderColor: '#fff',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    }

    // Actualizar inicialmente
    actualizarContadores();

    // Polling cada 10 segundos
    setInterval(actualizarContadores, 10000);
});
</script>