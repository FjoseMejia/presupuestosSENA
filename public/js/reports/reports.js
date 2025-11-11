document.addEventListener('DOMContentLoaded', function () {
    // =====================================================================
    // CONFIGURACIÓN INICIAL Y DECLARACIÓN DE VARIABLES GLOBALES
    // =====================================================================

    let tablaOriginalHTML = '';      
    let footerOriginalHTML = '';    
    let contadoresOriginalesHTML = ''; 
    let graficoResumen = null;      

    const modal = document.getElementById('modalReporte');
    const weekLabel = document.getElementById('modal-week-label');
    const inputWeek = document.getElementById('input-week');
    const inputSemanaId = document.getElementById('input-semana-id');
    const triggers = document.querySelectorAll('.btn-open-modal');

    const modalDetalles = document.getElementById('modalDetalles');
    const filtroConcepto = document.getElementById('filtro-concepto');
    const inputBusqueda = document.getElementById('modal-dependency-input');
    const datalistOpciones = document.getElementById('dependencias-list');
    const btnBuscar = document.getElementById('btn-modal-buscar');
    const btnLimpiar = document.getElementById('btn-limpiar-filtros');
    const btnRefrescar = document.getElementById('btn-refrescar');

    // =====================================================================
    // INICIALIZACIÓN DE ESTILOS Y CONFIGURACIÓN VISUAL
    // =====================================================================

    const inicializarEstilos = () => {
        const estilos = `
            .swal2-popup {
                border-radius: 15px;
                padding: 2rem;
            }
            .upload-progress-container {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 20px;
                margin: 15px 0;
            }
            .progress {
                border-radius: 10px;
                overflow: hidden;
            }
            .progress-bar {
                transition: width 0.6s ease;
            }
            .upload-status {
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 5px;
            }
            .upload-details {
                font-size: 0.9em;
                color: #7f8c8d;
            }
            #modal-dependency-input:disabled {
                background-color: #f8f9fa;
                cursor: not-allowed;
            }
            .filter-loading {
                opacity: 0.6;
                pointer-events: none;
            }
            .valor-negativo {
                color: #dc3545 !important;
                font-weight: 600;
            }
            .valor-negativo::before {
                content: "-$";
            }
            .valor-positivo::before {
                content: "$";
            }
            #graficoResumen {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-radius: 10px;
                padding: 15px;
                min-height: 300px;
            }
            .card-body canvas {
                transition: all 0.3s ease;
            }
            .card-body canvas:hover {
                transform: scale(1.01);
            }
            @media (max-width: 768px) {
                #graficoResumen {
                    height: 250px !important;
                }
            }
            .chart-container {
                position: relative;
                height: 300px;
                width: 100%;
            }
        `;

        const hojaEstilos = document.createElement('style');
        hojaEstilos.textContent = estilos;
        document.head.appendChild(hojaEstilos);
    };

    inicializarEstilos();

    // =====================================================================
    // FUNCIONALIDAD DE ACTUALIZACIÓN DEL TÍTULO DINÁMICO
    // =====================================================================

    const actualizarTituloBusqueda = (tipoFiltro, valorBusqueda) => {
        const tituloDinamico = document.getElementById('titulo-dinamico');
        if (!tituloDinamico) return;

        const textosFiltro = {
            '1': `Filtrado por Dependencia: "${valorBusqueda}"`,
            '2': `Filtrado por CDP: "${valorBusqueda}"`, 
            '3': `Filtrado por Concepto: "${valorBusqueda}"`
        };

        if (!tipoFiltro || !valorBusqueda) {
            tituloDinamico.textContent = 'Detalles Presupuestales Completos';
        } else {
            tituloDinamico.textContent = textosFiltro[tipoFiltro] || 'Resultados de Búsqueda';
        }
    };

    // =====================================================================
    // SISTEMA DE GRÁFICOS - FUNCIONALIDAD COMPLETA
    // =====================================================================

    const inicializarSistemaGraficos = () => {
        const canvas = document.getElementById('graficoResumen');
        if (!canvas) return;

        if (graficoResumen) {
            graficoResumen.destroy();
            graficoResumen = null;
        }

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const configGrafico = {
            type: 'bar',
            data: {
                labels: ['Valor Inicial', 'Valor Actual', 'Comprometido', 'Saldo', 'Pagado'],
                datasets: [{
                    label: 'Montos (Billones)',
                    data: [0, 0, 0, 0, 0],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'Distribución Presupuestal',
                        font: { size: 16, weight: 'bold' }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                return `$${(value * 1000).toLocaleString('es-ES')} millones`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value === 0) return '$0';
                                if (value >= 1000) return `$${(value/1000).toFixed(0)}B`;
                                if (value >= 1) return `$${value.toFixed(0)}M`;
                                return `$${value}`;
                            }
                        }
                    }
                }
            }
        };

        try {
            graficoResumen = new Chart(ctx, configGrafico);
            
            const toggleGrafico = document.getElementById('toggleGrafico');
            if (toggleGrafico) {
                toggleGrafico.addEventListener('change', function() {
                    const cardBody = canvas.closest('.card-body');
                    if (cardBody) {
                        cardBody.style.display = this.checked ? 'block' : 'none';
                        if (this.checked && graficoResumen) {
                            setTimeout(() => { graficoResumen.update(); }, 100);
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Error creando el gráfico:', error);
        }
    };

    const actualizarGraficoDesdeFooter = () => {
        if (!graficoResumen) {
            inicializarSistemaGraficos();
            return;
        }

        const footer = modalDetalles ? modalDetalles.querySelector('.card-footer') : null;
        if (!footer) return;

        const valores = {
            valorInicial: extraerValorDeElemento(footer, 'Valor Inicial'),
            valorActual: extraerValorDeElemento(footer, 'Valor Actual'), 
            comprometido: extraerValorDeElemento(footer, 'Comprometido'),
            saldo: extraerValorDeElemento(footer, 'Saldo'),
            pagado: extraerValorDeElemento(footer, 'Pagado')
        };

        const datos = [
            valores.valorInicial / 1000000000,
            valores.valorActual / 1000000000,
            valores.comprometido / 1000000000,
            valores.saldo / 1000000000,
            valores.pagado / 1000000000
        ];

        graficoResumen.data.datasets[0].data = datos;
        graficoResumen.update();
    };

    const extraerValorDeElemento = (footer, textoBusqueda) => {
        const elementos = footer.querySelectorAll('small');
        
        for (let elemento of elementos) {
            if (elemento.textContent.includes(textoBusqueda)) {
                const spanValor = elemento.querySelector('span.fw-bold');
                if (spanValor) {
                    const textoValor = spanValor.textContent.trim();
                    const numero = textoValor.replace('$', '').replace(/\./g, '');
                    return parseFloat(numero) || 0;
                }
            }
        }
        return 0;
    };

    // =====================================================================
    // GESTIÓN DEL ESTADO ORIGINAL DE LA TABLA
    // =====================================================================

    const guardarEstadoOriginal = () => {
        if (!modalDetalles) return;

        const tbody = modalDetalles.querySelector('.table tbody');
        if (tbody) tablaOriginalHTML = tbody.innerHTML;

        const footer = modalDetalles.querySelector('.card-footer');
        if (footer) footerOriginalHTML = footer.innerHTML;

        const contadorPrincipal = modalDetalles.querySelector('.card-body .text-end small');
        if (contadorPrincipal) contadoresOriginalesHTML = contadorPrincipal.innerHTML;

        setTimeout(() => {
            inicializarSistemaGraficos();
            setTimeout(() => { actualizarGraficoDesdeFooter(); }, 800);
        }, 100);
    };

    const restaurarEstadoOriginal = () => {
        if (!modalDetalles) return;

        const tbody = modalDetalles.querySelector('.table tbody');
        if (tbody && tablaOriginalHTML) tbody.innerHTML = tablaOriginalHTML;

        const footer = modalDetalles.querySelector('.card-footer');
        if (footer && footerOriginalHTML) footer.innerHTML = footerOriginalHTML;

        const contadorPrincipal = modalDetalles.querySelector('.card-body .text-end small');
        if (contadorPrincipal && contadoresOriginalesHTML) contadorPrincipal.innerHTML = contadoresOriginalesHTML;

        const tituloDinamico = document.getElementById('titulo-dinamico');
        if (tituloDinamico) tituloDinamico.textContent = 'Detalles Presupuestales Completos';

        const badgeContador = modalDetalles.querySelector('.card-header .badge.bg-primary');
        if (badgeContador && tablaOriginalHTML) {
            const divTemporal = document.createElement('div');
            divTemporal.innerHTML = tablaOriginalHTML;
            const filas = divTemporal.querySelectorAll('tr');
            const filasValidas = Array.from(filas).filter(fila => 
                !fila.querySelector('td[colspan]') && 
                !fila.textContent.includes('No se encontraron registros')
            ).length;
            badgeContador.textContent = `${filasValidas} registros`;
        }

        setTimeout(() => { actualizarGraficoDesdeFooter(); }, 500);
    };

    if (modalDetalles) {
        modalDetalles.addEventListener('show.bs.modal', guardarEstadoOriginal);
        modalDetalles.addEventListener('shown.bs.modal', function() {
            setTimeout(() => {
                if (graficoResumen) graficoResumen.update();
                else {
                    inicializarSistemaGraficos();
                    setTimeout(() => { actualizarGraficoDesdeFooter(); }, 500);
                }
            }, 1000);
        });
    }

    // =====================================================================
    // UTILIDADES Y FUNCIONES HELPER
    // =====================================================================

    const mostrarLoading = (elemento = null) => {
        if (elemento) elemento.classList.add('filter-loading');
    };

    const ocultarLoading = (elemento = null) => {
        if (elemento) elemento.classList.remove('filter-loading');
    };

    const formatNumber = (num) => {
        const numero = Number(num) || 0;
        return new Intl.NumberFormat('es-ES').format(Math.abs(numero));
    };

    const getClaseValor = (valor) => {
        const numero = Number(valor) || 0;
        return numero < 0 ? 'valor-negativo' : 'valor-positivo';
    };

    const formatValorMonetario = (valor) => {
        const numero = Number(valor) || 0;
        const clase = getClaseValor(valor);
        return `<span class="${clase}">${formatNumber(numero)}</span>`;
    };

    const escapeHtml = (unsafe) => {
        if (!unsafe) return '';
        return unsafe
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    // =====================================================================
    // FUNCIONES PARA ACTUALIZAR LA TABLA CON RESULTADOS DE BÚSQUEDA
    // =====================================================================

    const generarColumnasObservaciones = (item) => {
        if (window.userRolId == 4) {
            let observacionesHtml = '';
            for (let i = 1; i <= 4; i++) {
                observacionesHtml += `
                    <td class="text-center">
                        <input type="text"
                            class="form-control form-control-sm observacion"
                            data-cdp="${escapeHtml(item.cdp || '')}"
                            data-periodo="${i}"
                            value="${escapeHtml(item[`observacion${i}`] || '')}">
                    </td>
                `;
            }
            observacionesHtml += `
                <td class="text-center">
                    <button class="btn btn-sm btn-success btn-guardar-observaciones"
                        data-cdp="${escapeHtml(item.cdp || '')}">
                        <i class="fas fa-save"></i>
                    </button>
                </td>
            `;
            return observacionesHtml;
        }
        return '';
    };

    const determinarClasesPorcentaje = (porcentajeCompromiso) => {
        let claseFila = '';
        let claseBadge = 'bg-secondary';
        const porcentaje = Math.abs(Number(porcentajeCompromiso) || 0);

        if (porcentaje === 0) {
            claseFila = 'table-danger';
            claseBadge = 'bg-danger';
        } else if (porcentaje === 100) {
            claseFila = 'table-success';
            claseBadge = 'bg-success';
        } else if (porcentaje > 80) {
            claseFila = 'table-warning';
            claseBadge = 'bg-warning text-dark';
        } else if (porcentaje > 50) {
            claseFila = 'table-info';
            claseBadge = 'bg-info text-dark';
        } else if (porcentaje > 0) {
            claseBadge = 'bg-primary';
        }

        return { claseFila, claseBadge };
    };

    const actualizarContadoresSuperiores = (data) => {
        if (!modalDetalles) return;
        
        const contadorPrincipal = modalDetalles.querySelector('.card-body .text-end small');
        if (contadorPrincipal) {
            contadorPrincipal.innerHTML = `<i class="fas fa-database me-1"></i>${data.length} registros encontrados`;
        }

        const badgeContador = modalDetalles.querySelector('.card-header .badge.bg-primary');
        if (badgeContador) badgeContador.textContent = `${data.length} registros`;
    };

    const actualizarFooterTotales = (count, valorInicial, valorOperaciones, presupuesto, saldo, comprometido, pagado) => {
        const footer = modalDetalles ? modalDetalles.querySelector('.card-footer') : null;
        if (!footer) return;
        
        const getClaseTotal = (valor) => {
            const numero = Number(valor) || 0;
            return numero < 0 ? 'text-danger' : '';
        };

        const formatTotal = (valor) => {
            const numero = Number(valor) || 0;
            const signo = numero < 0 ? '-$' : '$';
            return `${signo}${formatNumber(numero)}`;
        };
        
        footer.innerHTML = `
            <div class="row align-items-center">
                <div class="col-md-4">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Mostrando <span class="fw-bold">${count}</span> registros
                    </small>
                </div>
                <div class="col-md-8 text-end">
                    <div class="d-flex justify-content-end gap-3 flex-wrap">
                        <small class="text-muted">
                            Valor Inicial: <span class="fw-bold ${getClaseTotal(valorInicial)}">${formatTotal(valorInicial)}</span>
                        </small>
                        <small class="text-muted">
                            Valor Operaciones: <span class="fw-bold ${getClaseTotal(valorOperaciones)}">${formatTotal(valorOperaciones)}</span>
                        </small>
                        <small class="text-muted">
                            Valor Actual: <span class="fw-bold ${getClaseTotal(presupuesto)}">${formatTotal(presupuesto)}</span>
                        </small>
                        <small class="text-muted">
                            Comprometido: <span class="fw-bold ${getClaseTotal(comprometido)}">${formatTotal(comprometido)}</span>
                        </small>
                        <small class="text-muted">
                            Saldo: <span class="fw-bold ${getClaseTotal(saldo)}">${formatTotal(saldo)}</span>
                        </small>
                        <small class="text-muted">
                            Pagado: <span class="fw-bold ${getClaseTotal(pagado)}">${formatTotal(pagado)}</span>
                        </small>
                    </div>
                </div>
            </div>
        `;
    };

    const actualizarTablaResultados = (data) => {
        try {
            let tbody = null;
            if (modalDetalles) tbody = modalDetalles.querySelector('.table tbody');
            if (!tbody) throw new Error('No se pudo encontrar el tbody en el modalDetalles');
            
            tbody.innerHTML = '';
            if (!data || !Array.isArray(data)) throw new Error('Datos inválidos recibidos del servidor');
            
            actualizarContadoresSuperiores(data);
            
            if (data.length === 0) {
                let theadRow = modalDetalles ? modalDetalles.querySelector('thead tr') : null;
                const colspan = theadRow ? theadRow.cells.length : 16;
                tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-muted py-5"><i class="fas fa-search fa-2x mb-3 d-block"></i>No se encontraron registros para esta búsqueda</td></tr>`;
                return;
            }

            let totalValorInicial = 0;
            let totalValorOperaciones = 0;
            let totalPresupuesto = 0;
            let totalSaldo = 0;
            let totalComprometido = 0;
            let totalValorPagado = 0;

            data.forEach((item, index) => {
                const valorInicial = Number(item.valorInicial) || 0;
                const valorOperaciones = Number(item.valorOperaciones) || 0;
                const valorActual = Number(item.valorActual) || 0;
                const saldoComprometer = Number(item.saldoComprometer) || 0;
                const valorComprometido = Number(item.valorComprometido) || 0;
                const porcentajeCompromiso = Number(item.porcentajeCompromiso) || 0;
                const valorPagado = Number(item.valorPagado) || 0;
                const porcentajePagado = Number(item.porcentajePagado) || 0;

                totalValorInicial += valorInicial;
                totalValorOperaciones += valorOperaciones;
                totalPresupuesto += valorActual;
                totalSaldo += saldoComprometer;
                totalComprometido += valorComprometido;
                totalValorPagado += valorPagado;

                const { claseFila, claseBadge } = determinarClasesPorcentaje(porcentajeCompromiso);
                const tr = document.createElement('tr');
                tr.className = claseFila;
                
                const fechaFormateada = item.fechaRegistro ? 
                    new Date(item.fechaRegistro).toLocaleDateString('es-ES') : '';

                const filaHTML = `
                    <td class="text-center fw-bold">${escapeHtml(item.cdp || '')}</td>
                    <td class="text-center">${fechaFormateada}</td>
                    <td class="text-center">${escapeHtml(item.codigo || '')}</td>
                    <td class="text-center">${escapeHtml(item.dependenciaDescripcion || '')}</td>
                    <td class="small" title="${escapeHtml(item.descripcionCompleta || 'Sin descripción')}">${escapeHtml(item.objeto || 'N/A')}</td>
                    <td>${escapeHtml(item.rubro || '')}</td>
                    <td class="small" title="${escapeHtml(item.descripcionRubro || '')}">${escapeHtml(item.descripcionRubro || '')}</td>
                    <td class="text-center">${escapeHtml(item.fuente || '')}</td>
                    <td class="text-end">${formatValorMonetario(valorInicial)}</td>
                    <td class="text-end">${formatValorMonetario(valorOperaciones)}</td>
                    <td class="text-end fw-bold">${formatValorMonetario(valorActual)}</td>
                    <td class="text-end">${formatValorMonetario(saldoComprometer)}</td>
                    <td class="text-end">${formatValorMonetario(valorComprometido)}</td>
                    <td class="text-center"><span class="badge ${claseBadge}">${Math.abs(porcentajeCompromiso).toFixed(1)}%</span></td>
                    <td class="text-end">${formatValorMonetario(valorPagado)}</td>
                    <td class="text-center"><span class="badge bg-secondary">${Math.abs(porcentajePagado).toFixed(1)}%</span></td>
                    ${generarColumnasObservaciones(item)}
                `;

                tr.innerHTML = filaHTML;
                tbody.appendChild(tr);
            });

            actualizarFooterTotales(data.length, totalValorInicial, totalValorOperaciones, totalPresupuesto, totalSaldo, totalComprometido, totalValorPagado);
            setTimeout(() => { actualizarGraficoDesdeFooter(); }, 500);
            
        } catch (error) {
            console.error('Error en actualizarTablaResultados:', error);
            if (modalDetalles) {
                const tbody = modalDetalles.querySelector('tbody');
                if (tbody) tbody.innerHTML = `<tr><td colspan="20" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle me-2"></i>Error al mostrar los datos: ${error.message}</td></tr>`;
            }
            throw error;
        }
    };

    // =====================================================================
    // FUNCIONALIDAD DE FILTROS Y BÚSQUEDA
    // =====================================================================

    const inicializarFuncionalidadFiltros = () => {
        if (!filtroConcepto || !inputBusqueda) return;

        const endpointsFiltros = {
            '1': 'reports/dependencias',
            '2': 'reports/cdps',
            '3': 'reports/conceptos'
        };

        const placeholdersFiltros = {
            '1': 'Buscar por dependencia...',
            '2': 'Buscar por número CDP...', 
            '3': 'Buscar por concepto...'
        };

        const cargarOpcionesDatalist = (tipoFiltro) => {
            const endpoint = endpointsFiltros[tipoFiltro];
            if (!endpoint) return;

            mostrarLoading(inputBusqueda);
            fetch(endpoint)
                .then(response => {
                    if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    datalistOpciones.innerHTML = '';
                    if (Array.isArray(data)) {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            let valor = '';
                            if (typeof item === 'string' || typeof item === 'number') {
                                valor = String(item);
                            } else if (typeof item === 'object' && item !== null) {
                                switch(tipoFiltro) {
                                    case '1': valor = item.dependenciaDescripcion || item.nombre || item.id || ''; break;
                                    case '2': valor = item.cdp || item.numero || item.id || ''; break;
                                    case '3': valor = item.descripcionCompleta || item.concepto || item.nombre || item.id || ''; break;
                                    default: valor = '';
                                }
                            }
                            if (valor && valor.trim() !== '') {
                                option.value = valor;
                                datalistOpciones.appendChild(option);
                            }
                        });
                    }
                    ocultarLoading(inputBusqueda);
                })
                .catch(error => {
                    console.error('Error en la carga de opciones del filtro:', error);
                    ocultarLoading(inputBusqueda);
                    Swal.fire('Error', 'No se pudieron cargar las opciones de búsqueda', 'error');
                });
        };

        const ejecutarBusqueda = () => {
            const filtroSeleccionado = filtroConcepto.value;
            const valorBusqueda = inputBusqueda.value.trim();
            
            actualizarTituloBusqueda(filtroSeleccionado, valorBusqueda);
            
            if (!filtroSeleccionado) {
                Swal.fire('Advertencia', 'Por favor seleccione un tipo de filtro', 'warning');
                return;
            }

            if (!valorBusqueda) {
                Swal.fire('Advertencia', 'Por favor ingrese un valor para buscar', 'warning');
                return;
            }

            const semanaLabel = document.getElementById('modal-detalles-week-label');
            const semana = semanaLabel ? semanaLabel.textContent.replace('Semana ', '') : '';
            if (!semana) {
                Swal.fire('Error', 'No se pudo determinar la semana para la búsqueda', 'error');
                return;
            }

            const parametrosBusqueda = new URLSearchParams({
                tipoFiltro: filtroSeleccionado,
                valor: valorBusqueda,
                semana: semana
            });

            mostrarLoading(btnBuscar);
            const tablaResponsive = modalDetalles.querySelector('.table-responsive');
            if (tablaResponsive) mostrarLoading(tablaResponsive);

            fetch(`api/buscar-informe?${parametrosBusqueda}`)
                .then(async (response) => {
                    if (!response.ok && response.status !== 200) {
                        const errorTexto = await response.text();
                        throw new Error(`Error ${response.status}: ${response.statusText}. ${errorTexto}`);
                    }
                    return response.json();
                })
                .then(data => {
                    actualizarTablaResultados(data);
                    ocultarLoading(btnBuscar);
                    if (tablaResponsive) ocultarLoading(tablaResponsive);
                })
                .catch(error => {
                    console.error('Error en la ejecución de la búsqueda:', error);
                    ocultarLoading(btnBuscar);
                    if (tablaResponsive) ocultarLoading(tablaResponsive);
                    Swal.fire('Error', error.message, 'error');
                });
        };

        filtroConcepto.addEventListener('change', function() {
            const filtroSeleccionado = this.value;
            inputBusqueda.value = '';
            datalistOpciones.innerHTML = '';
            
            if (filtroSeleccionado === '') {
                inputBusqueda.disabled = true;
                inputBusqueda.placeholder = 'Seleccione un filtro primero...';
            } else {
                inputBusqueda.disabled = false;
                inputBusqueda.placeholder = placeholdersFiltros[filtroSeleccionado];
                cargarOpcionesDatalist(filtroSeleccionado);
            }
        });

        btnBuscar.addEventListener('click', ejecutarBusqueda);
        inputBusqueda.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') ejecutarBusqueda();
        });

        btnLimpiar.addEventListener('click', function() {
            filtroConcepto.value = '';
            inputBusqueda.value = '';
            inputBusqueda.disabled = true;
            inputBusqueda.placeholder = 'Seleccione un filtro primero...';
            datalistOpciones.innerHTML = '';
            restaurarEstadoOriginal();
            Swal.fire({ icon: 'success', title: 'Filtros limpiados', text: 'La tabla ha sido restaurada a su estado original', timer: 2000, showConfirmButton: false });
        });

        if (btnRefrescar) {
            btnRefrescar.addEventListener('click', function() {
                restaurarEstadoOriginal();
                Swal.fire({ icon: 'success', title: 'Datos actualizados', text: 'Tabla restaurada al estado original', timer: 1500, showConfirmButton: false });
            });
        }
    };

    inicializarFuncionalidadFiltros();

    // =====================================================================
    // FUNCIONALIDAD DEL MODAL DE SUBIDA DE REPORTES
    // =====================================================================

    const inicializarModalReportes = () => {
        triggers.forEach(boton => {
            boton.addEventListener('click', () => {
                const semana = boton.getAttribute('data-week');
                const semanaId = boton.getAttribute('data-semana-id');
                if (weekLabel) weekLabel.textContent = '- ' + semana;
                if (inputWeek) inputWeek.value = semana;
                if (inputSemanaId) inputSemanaId.value = semanaId;
            });
        });
    };

    inicializarModalReportes();

    // =====================================================================
    // FUNCIONALIDAD DE SUBIDA DE ARCHIVOS
    // =====================================================================

    const inicializarSubidaArchivos = () => {
        const formReporte = document.getElementById('formReporte');
        if (!formReporte) return;

        formReporte.addEventListener('submit', async function (e) {
            e.preventDefault();

            const weekValue = inputWeek ? inputWeek.value : '';
            const semanaIdValue = inputSemanaId ? inputSemanaId.value : '';
            if (!weekValue || !semanaIdValue) {
                Swal.fire('Error', 'Datos de semana incompletos.', 'error');
                return;
            }

            const fileCdp = document.getElementById('file-cdp').files[0];
            const fileRp = document.getElementById('file-rp').files[0];
            const filePagos = document.getElementById('file-pagos').files[0];

            if (!fileCdp && !fileRp && !filePagos) {
                Swal.fire('Error', 'Debe seleccionar al menos un archivo.', 'warning');
                return;
            }

            let alertaLoading = Swal.fire({
                title: 'Subiendo archivos...',
                html: `<div class="text-center"><div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Subiendo...</span></div><p class="mb-1">Procesando archivos Excel</p><p class="small text-muted">Esto puede tomar unos minutos</p></div>`,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const formData = new FormData();
                formData.append('week', weekValue);
                formData.append('semana_id', semanaIdValue);
                if (fileCdp) formData.append('cdp', fileCdp);
                if (fileRp) formData.append('rp', fileRp);
                if (filePagos) formData.append('pagos', filePagos);

                const response = await fetch(formReporte.action, { method: 'POST', body: formData });
                Swal.close();
                const resultado = await response.json();

                Swal.fire({ title: resultado.titulo, text: resultado.texto, icon: resultado.icono, confirmButtonText: 'Aceptar' }).then(() => {
                    if (resultado.icono === 'success') {
                        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalReporte'));
                        if (modalInstance) modalInstance.hide();
                        window.location.reload();
                    }
                });

            } catch (error) {
                console.error('Error en la subida de archivos:', error);
                Swal.close();
                Swal.fire('Error', 'Ocurrió un error inesperado al subir los archivos.', 'error');
            }
        });
    };

    inicializarSubidaArchivos();

    // =====================================================================
    // FUNCIONALIDAD DEL MODAL DE DESCARGAS
    // =====================================================================

    const inicializarModalDescargas = () => {
        const modalDescargas = document.getElementById('modalDescargas');
        if (!modalDescargas) return;

        modalDescargas.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const week = button.getAttribute('data-week');
            const semanaId = button.getAttribute('data-semana-id');
            const archivoCdp = button.getAttribute('data-archivo-cdp');
            const archivoRp = button.getAttribute('data-archivo-rp');
            const archivoPagos = button.getAttribute('data-archivo-pagos');
            const urlCdp = button.getAttribute('data-url-cdp');
            const urlRp = button.getAttribute('data-url-rp');
            const urlPagos = button.getAttribute('data-url-pagos');

            const subtitulo = document.getElementById('descargas-subtitulo');
            if (subtitulo) subtitulo.textContent = `Archivos disponibles para ${week}:`;

            const listaDescargas = document.getElementById('lista-descargas');
            if (!listaDescargas) return;

            listaDescargas.innerHTML = '';

            const agregarDescarga = (url, archivo, tipo, texto, descripcion, icono, colorIcono) => {
                if (archivo && archivo.trim() !== '' && url && url.trim() !== '') {
                    const enlace = document.createElement('a');
                    enlace.href = url;
                    enlace.className = 'list-group-item list-group-item-action d-flex align-items-center';
                    enlace.download = archivo;
                    enlace.innerHTML = `
                        <i class="fas ${icono} ${colorIcono} me-2"></i>
                        <div class="flex-grow-1">
                            <small class="fw-semibold">${texto}</small>
                            <br>
                            <small class="text-muted">${descripcion}</small>
                        </div>
                        <i class="fas fa-download text-primary"></i>
                    `;
                    listaDescargas.appendChild(enlace);
                    return true;
                }
                return false;
            };

            let archivosDisponibles = 0;
            if (agregarDescarga(urlCdp, archivoCdp, 'cdp', 'CDP', 'Certificado Disponibilidad', 'fa-file-excel', 'text-success')) archivosDisponibles++;
            if (agregarDescarga(urlRp, archivoRp, 'rp', 'R.P', 'Registro Presupuestal', 'fa-file-invoice-dollar', 'text-warning')) archivosDisponibles++;
            if (agregarDescarga(urlPagos, archivoPagos, 'pagos', 'Pagos', 'Registro de Pagos', 'fa-receipt', 'text-info')) archivosDisponibles++;

            if (archivosDisponibles === 0) {
                listaDescargas.innerHTML = `<div class="text-center text-muted py-3"><i class="fas fa-exclamation-circle me-1"></i>No hay archivos disponibles para descargar</div>`;
            }
        });
    };

    inicializarModalDescargas();
});