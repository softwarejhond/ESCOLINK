
<style>
.card-glass {
    position: relative;
    overflow: hidden;
    background: transparent !important;
    border-radius: 16px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-glass:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.15) !important;
}

.card-glass::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: -2;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.card-glass::after {
    content: "";
    position: absolute;
    inset: 0;
    z-index: -1;
    background-color: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}

.card-glass .card-body {
    position: relative;
    z-index: 1;
}

.card-glass.patron-1::before { background-image: url('img/patron_1.svg'); }
.card-glass.patron-2::before { background-image: url('img/patron_2.svg'); }
.card-glass.patron-3::before { background-image: url('img/patron_3.svg'); }
.card-glass.patron-4::before { background-image: url('img/patron_1.svg'); }

.trend-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 600;
}
.trend-badge.up {
    background: rgba(34, 197, 94, 0.15);
    color: #16a34a;
}
.trend-badge.neutral {
    background: rgba(99, 102, 241, 0.15);
    color: #6366f1;
}

/* Gráficos compactos */
.chart-card {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: box-shadow 0.3s ease;
}
.chart-card:hover {
    box-shadow: 0 6px 16px rgba(0,0,0,0.1);
}
.chart-card .card-header {
    padding: 10px 16px !important;
}
.chart-card .card-header h6 {
    font-size: 0.82rem;
}
.chart-card .card-body {
    padding: 12px 14px !important;
}
.chart-card .card-body.chart-body-donut {
    padding: 8px 10px !important;
    min-height: 200px;
}

.chart-header-indigo { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.chart-header-teal { background: linear-gradient(135deg, #14b8a6, #06b6d4); }
.chart-header-rose { background: linear-gradient(135deg, #f43f5e, #ec4899); }
.chart-header-amber { background: linear-gradient(135deg, #f59e0b, #f97316); }
.chart-header-emerald { background: linear-gradient(135deg, #10b981, #34d399); }
.chart-header-violet { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
</style>

<!-- ============ FILA DE CONTADORES ============ -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-glass patron-1 text-center border-0 shadow-lg h-100">
            <div class="card-body d-flex flex-column justify-content-center py-4">
                <div class="mb-2">
                    <span class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="bi bi-mortarboard-fill text-primary" style="font-size: 1.8rem;"></i>
                    </span>
                </div>
                <h6 class="card-title mt-2 text-muted fw-semibold text-uppercase" style="font-size:0.78rem; letter-spacing:0.5px;">Total Estudiantes</h6>
                <h2 id="totalEstudiantes" class="fw-bold mb-1" style="color: #1e293b; font-size: 2.2rem;">0</h2>
                <span class="trend-badge neutral"><i class="bi bi-database"></i> Registrados</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-glass patron-2 text-center border-0 shadow-lg h-100">
            <div class="card-body d-flex flex-column justify-content-center py-4">
                <div class="mb-2">
                    <span class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="bi bi-person-check-fill text-success" style="font-size: 1.8rem;"></i>
                    </span>
                </div>
                <h6 class="card-title mt-2 text-muted fw-semibold text-uppercase" style="font-size:0.78rem; letter-spacing:0.5px;">Estudiantes Activos</h6>
                <h2 id="estudiantesActivos" class="fw-bold mb-1" style="color: #1e293b; font-size: 2.2rem;">0</h2>
                <span class="trend-badge up"><i class="bi bi-check-circle"></i> Activos</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-glass patron-3 text-center border-0 shadow-lg h-100">
            <div class="card-body d-flex flex-column justify-content-center py-4">
                <div class="mb-2">
                    <span class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="bi bi-calendar-plus-fill text-warning" style="font-size: 1.8rem;"></i>
                    </span>
                </div>
                <h6 class="card-title mt-2 text-muted fw-semibold text-uppercase" style="font-size:0.78rem; letter-spacing:0.5px;">Registros Este Mes</h6>
                <h2 id="registrosMes" class="fw-bold mb-1" style="color: #1e293b; font-size: 2.2rem;">0</h2>
                <span class="trend-badge up"><i class="bi bi-arrow-up-short"></i> Este mes</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-glass patron-4 text-center border-0 shadow-lg h-100">
            <div class="card-body d-flex flex-column justify-content-center py-4">
                <div class="mb-2">
                    <span class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="bi bi-building-fill text-info" style="font-size: 1.8rem;"></i>
                    </span>
                </div>
                <h6 class="card-title mt-2 text-muted fw-semibold text-uppercase" style="font-size:0.78rem; letter-spacing:0.5px;">Sedes Activas</h6>
                <h2 id="totalSedes" class="fw-bold mb-1" style="color: #1e293b; font-size: 2.2rem;">0</h2>
                <span class="trend-badge neutral"><i class="bi bi-geo-alt"></i> Sedes</span>
            </div>
        </div>
    </div>
</div>

<!-- ============ FILA DE GRÁFICOS 1: Sedes + Género + Estado ============ -->
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card chart-card h-100">
            <div class="card-header chart-header-indigo text-white py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-bar-chart-fill me-2"></i>Estudiantes por Sede</h6>
            </div>
            <div class="card-body">
                <canvas id="chartSedes" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card chart-card h-100">
            <div class="card-header chart-header-rose text-white py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-pie-chart-fill me-2"></i>Género</h6>
            </div>
            <div class="card-body chart-body-donut d-flex align-items-center justify-content-center">
                <canvas id="chartGenero"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card chart-card h-100">
            <div class="card-header chart-header-violet text-white py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-toggles me-2"></i>Estado</h6>
            </div>
            <div class="card-body chart-body-donut d-flex align-items-center justify-content-center">
                <canvas id="chartEstatus"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ============ FILA DE GRÁFICOS 2: Tendencia + Grados ============ -->
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card chart-card h-100">
            <div class="card-header chart-header-teal text-white py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-graph-up me-2"></i>Registros por Mes (últimos 12 meses)</h6>
            </div>
            <div class="card-body">
                <canvas id="chartRegistrosMes" height="130"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card chart-card h-100">
            <div class="card-header chart-header-amber text-white py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-list-ol me-2"></i>Estudiantes por Grado</h6>
            </div>
            <div class="card-body">
                <canvas id="chartGrados" height="130"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ============ FILA DE GRÁFICOS 3: Comunas ============ -->
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card chart-card">
            <div class="card-header chart-header-emerald text-white py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-geo-alt-fill me-2"></i>Top 10 Comunas</h6>
            </div>
            <div class="card-body">
                <canvas id="chartComunas" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let charts = {};

    const palette = {
        primary: [
            'rgba(99, 102, 241, 0.7)', 'rgba(236, 72, 153, 0.7)',
            'rgba(20, 184, 166, 0.7)', 'rgba(249, 115, 22, 0.7)',
            'rgba(34, 197, 94, 0.7)', 'rgba(139, 92, 246, 0.7)',
            'rgba(14, 165, 233, 0.7)', 'rgba(244, 63, 94, 0.7)',
            'rgba(245, 158, 11, 0.7)', 'rgba(16, 185, 129, 0.7)'
        ],
        borders: [
            'rgba(99, 102, 241, 1)', 'rgba(236, 72, 153, 1)',
            'rgba(20, 184, 166, 1)', 'rgba(249, 115, 22, 1)',
            'rgba(34, 197, 94, 1)', 'rgba(139, 92, 246, 1)',
            'rgba(14, 165, 233, 1)', 'rgba(244, 63, 94, 1)',
            'rgba(245, 158, 11, 1)', 'rgba(16, 185, 129, 1)'
        ],
        genero: {
            bg: ['rgba(59, 130, 246, 0.7)', 'rgba(236, 72, 153, 0.7)', 'rgba(168, 162, 158, 0.7)'],
            border: ['rgba(59, 130, 246, 1)', 'rgba(236, 72, 153, 1)', 'rgba(168, 162, 158, 1)']
        }
    };

    // Opciones globales compactas
    const compactScaleOpts = {
        ticks: { font: { size: 10 }, padding: 4 },
        grid: { drawTicks: false }
    };

    const compactTooltip = {
        padding: 8,
        bodyFont: { size: 11 },
        titleFont: { size: 11 }
    };

    function actualizarContadores() {
        fetch('components/cardContadores/actualizarContadores.php')
            .then(r => r.json())
            .then(data => {
                animateCounter('totalEstudiantes', data.totalEstudiantes);
                animateCounter('estudiantesActivos', data.estudiantesActivos);
                animateCounter('registrosMes', data.registrosMes);
                animateCounter('totalSedes', data.totalSedes);

                renderChartSedes(data);
                renderChartGenero(data);
                renderChartRegistrosMes(data);
                renderChartGrados(data);
                renderChartComunas(data);
                renderChartEstatus(data);
            })
            .catch(e => console.error('Error al actualizar contadores:', e));
    }

    function animateCounter(id, target) {
        const el = document.getElementById(id);
        if (!el) return;
        target = parseInt(target) || 0;
        const start = parseInt(el.textContent) || 0;
        if (start === target) return;
        const duration = 1000, step = (target - start) / (duration / 30);
        let current = start;
        const timer = setInterval(() => {
            current += step;
            if ((step > 0 && current >= target) || (step < 0 && current <= target) || step === 0) {
                current = target; clearInterval(timer);
            }
            el.textContent = Math.floor(current).toLocaleString('es-CO');
        }, 30);
    }

    function destroyChart(name) {
        if (charts[name]) { charts[name].destroy(); charts[name] = null; }
    }

    function pctLabel(ctx) {
        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
        const pct = ((ctx.parsed / total) * 100).toFixed(1);
        return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
    }

    // 1 — Estudiantes por Sede
    function renderChartSedes(data) {
        destroyChart('sedes');
        if (!data.labelsSedes?.length) return;
        charts.sedes = new Chart(document.getElementById('chartSedes'), {
            type: 'bar',
            data: {
                labels: data.labelsSedes,
                datasets: [{
                    data: data.valuesSedes,
                    backgroundColor: palette.primary,
                    borderColor: palette.borders,
                    borderWidth: 1, borderRadius: 4, maxBarThickness: 38
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { ...compactTooltip, callbacks: { label: c => `${c.parsed.y} estudiantes` } }
                },
                scales: {
                    y: { beginAtZero: true, ...compactScaleOpts, ticks: { ...compactScaleOpts.ticks, precision: 0 } },
                    x: { ...compactScaleOpts, ticks: { ...compactScaleOpts.ticks, maxRotation: 35 } }
                }
            }
        });
    }

    // 2 — Género (dona)
    function renderChartGenero(data) {
        destroyChart('genero');
        if (!data.labelsGenero?.length) return;
        charts.genero = new Chart(document.getElementById('chartGenero'), {
            type: 'doughnut',
            data: {
                labels: data.labelsGenero,
                datasets: [{
                    data: data.valuesGenero,
                    backgroundColor: palette.genero.bg,
                    borderColor: palette.genero.border,
                    borderWidth: 2, hoverOffset: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true, cutout: '58%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 10, usePointStyle: true, pointStyle: 'circle', font: { size: 10 } } },
                    tooltip: { ...compactTooltip, callbacks: { label: pctLabel } }
                }
            }
        });
    }

    // 3 — Registros por Mes (línea)
    function renderChartRegistrosMes(data) {
        destroyChart('registrosMes');
        if (!data.labelsMeses?.length) return;
        const mesesNombre = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        const labels = data.labelsMeses.map(m => {
            const [y, mo] = m.split('-');
            return `${mesesNombre[parseInt(mo)-1]} ${y.slice(2)}`;
        });
        charts.registrosMes = new Chart(document.getElementById('chartRegistrosMes'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    data: data.valoresMeses,
                    fill: true,
                    borderColor: 'rgba(20, 184, 166, 1)',
                    backgroundColor: 'rgba(20, 184, 166, 0.08)',
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(20, 184, 166, 1)',
                    pointBorderColor: '#fff', pointBorderWidth: 2,
                    pointRadius: 4, pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { ...compactTooltip, callbacks: { label: c => `${c.parsed.y} registros` } }
                },
                scales: {
                    y: { beginAtZero: true, ...compactScaleOpts, ticks: { ...compactScaleOpts.ticks, precision: 0 } },
                    x: { ...compactScaleOpts }
                }
            }
        });
    }

    // 4 — Grados (barras horizontales)
    function renderChartGrados(data) {
        destroyChart('grados');
        if (!data.labelsGrados?.length) return;
        charts.grados = new Chart(document.getElementById('chartGrados'), {
            type: 'bar',
            data: {
                labels: data.labelsGrados,
                datasets: [{
                    data: data.valuesGrados,
                    backgroundColor: 'rgba(249, 115, 22, 0.55)',
                    borderColor: 'rgba(249, 115, 22, 1)',
                    borderWidth: 1, borderRadius: 3, maxBarThickness: 20
                }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { ...compactTooltip, callbacks: { label: c => `${c.parsed.x} estudiantes` } }
                },
                scales: {
                    x: { beginAtZero: true, ...compactScaleOpts, ticks: { ...compactScaleOpts.ticks, precision: 0 } },
                    y: { ...compactScaleOpts }
                }
            }
        });
    }

    // 5 — Comunas (barras)
    function renderChartComunas(data) {
        destroyChart('comunas');
        if (!data.labelsComunas?.length) return;
        charts.comunas = new Chart(document.getElementById('chartComunas'), {
            type: 'bar',
            data: {
                labels: data.labelsComunas,
                datasets: [{
                    data: data.valuesComunas,
                    backgroundColor: 'rgba(16, 185, 129, 0.55)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1, borderRadius: 4, maxBarThickness: 50
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { ...compactTooltip, callbacks: { label: c => `${c.parsed.y} estudiantes` } }
                },
                scales: {
                    y: { beginAtZero: true, ...compactScaleOpts, ticks: { ...compactScaleOpts.ticks, precision: 0 } },
                    x: { ...compactScaleOpts, ticks: { ...compactScaleOpts.ticks, maxRotation: 35 } }
                }
            }
        });
    }

    // 6 — Estado (dona)
    function renderChartEstatus(data) {
        destroyChart('estatus');
        if (!data.labelsEstatus?.length) return;
        charts.estatus = new Chart(document.getElementById('chartEstatus'), {
            type: 'doughnut',
            data: {
                labels: data.labelsEstatus,
                datasets: [{
                    data: data.valuesEstatus,
                    backgroundColor: [
                        'rgba(34,197,94,0.7)', 'rgba(239,68,68,0.7)',
                        'rgba(245,158,11,0.7)', 'rgba(107,114,128,0.7)',
                        'rgba(59,130,246,0.7)'
                    ],
                    borderColor: [
                        'rgba(34,197,94,1)', 'rgba(239,68,68,1)',
                        'rgba(245,158,11,1)', 'rgba(107,114,128,1)',
                        'rgba(59,130,246,1)'
                    ],
                    borderWidth: 2, hoverOffset: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: true, cutout: '55%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 10, usePointStyle: true, pointStyle: 'circle', font: { size: 10 } } },
                    tooltip: { ...compactTooltip, callbacks: { label: pctLabel } }
                }
            }
        });
    }

    actualizarContadores();
    setInterval(actualizarContadores, 15000);
});
</script>