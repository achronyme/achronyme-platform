import Plotly from 'plotly.js-dist-min';
import { Constraint, Point3D } from './graphical-engine';

export const PlotlyAdapter = {
    elementId: 'graphicalChart',
    observer: null as MutationObserver | null,

    init() {
        // 1. Configuración inicial usando el tema actual
        const layout = {
            margin: { t: 30, l: 50, r: 20, b: 40 }, // Márgenes ajustados
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor: 'rgba(0,0,0,0)',
            ...this.getThemeLayout() // <--- Inyectamos colores dinámicos
        };

        // @ts-ignore
        Plotly.newPlot(this.elementId, [], layout, {
            responsive: true,
            displayModeBar: true,
            displaylogo: false,
            modeBarButtonsToRemove: ['lasso2d', 'select2d']
        });

        // 2. Activar el "Listener" de cambio de tema
        this.observeThemeChanges();
    },

    /**
     * Retorna la configuración de colores según si existe la clase 'dark' en HTML
     */
    getThemeLayout() {
        const isDark = document.documentElement.classList.contains('dark');

        // Paleta Slate de Tailwind
        const fontColor = isDark ? '#94a3b8' : '#1e293b'; // Slate-400 vs Slate-800
        const gridColor = isDark ? '#334155' : '#e2e8f0'; // Slate-700 vs Slate-200

        return {
            font: { color: fontColor },
            xaxis: {
                gridcolor: gridColor,
                zerolinecolor: gridColor,
                color: fontColor
            },
            yaxis: {
                gridcolor: gridColor,
                zerolinecolor: gridColor,
                color: fontColor
            },
            // Configuración específica para 3D
            scene: {
                xaxis: { gridcolor: gridColor, zerolinecolor: gridColor, color: fontColor, backgroundcolor: 'rgba(0,0,0,0)' },
                yaxis: { gridcolor: gridColor, zerolinecolor: gridColor, color: fontColor, backgroundcolor: 'rgba(0,0,0,0)' },
                zaxis: { gridcolor: gridColor, zerolinecolor: gridColor, color: fontColor, backgroundcolor: 'rgba(0,0,0,0)' }
            }
        };
    },

    /**
     * Observa cambios en la etiqueta <html> para detectar el toggle de Dark Mode
     */
    observeThemeChanges() {
        if (this.observer) return; // Evitar duplicados

        this.observer = new MutationObserver(() => {
            const element = document.getElementById(this.elementId);
            if (element) {
                // Actualiza SOLO los colores, manteniendo los datos y el zoom actual
                // @ts-ignore
                Plotly.relayout(this.elementId, this.getThemeLayout());
            }
        });

        this.observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'] // Solo nos importa si cambia la clase
        });
    },

    render2D(constraints: Constraint[], vertices: Point3D[], optimal: Point3D | null, range: number) {
        const traces: any[] = [];

        // Detectar tema para colores de relleno
        const isDark = document.documentElement.classList.contains('dark');
        const fillColor = isDark ? 'rgba(74, 222, 128, 0.2)' : 'rgba(75, 192, 192, 0.4)'; // Verde brillante tenue en dark

        // 1. Área Factible
        if (vertices.length >= 3) {
            const cx = vertices.reduce((s, p) => s + p.x, 0) / vertices.length;
            const cy = vertices.reduce((s, p) => s + p.y, 0) / vertices.length;
            const sorted = [...vertices].sort((a, b) => Math.atan2(a.y - cy, a.x - cx) - Math.atan2(b.y - cy, b.x - cx));
            sorted.push(sorted[0]);

            traces.push({
                x: sorted.map(p => p.x),
                y: sorted.map(p => p.y),
                fill: 'toself',
                type: 'scatter',
                mode: 'lines',
                name: 'Región Factible',
                line: { width: 0 },
                fillcolor: fillColor,
                hoverinfo: 'skip'
            });
        }

        // 2. Líneas de Restricción
        constraints.forEach((c, i) => {
            let x = [0, range], y = [];
            if (Math.abs(c.x2) > 1e-9) y = x.map(v => (c.rhs - c.x1 * v) / c.x2);
            else { x = [c.rhs / c.x1, c.rhs / c.x1]; y = [0, range]; }

            traces.push({
                x, y,
                type: 'scatter',
                mode: 'lines',
                name: `R${i + 1}`,
                line: { dash: 'dash', width: 1, color: isDark ? '#64748b' : '#94a3b8' }
            });
        });

        this.addPointsTrace(traces, vertices, optimal, '2d');

        const layout = {
            title: 'Gráfico 2D',
            xaxis: { title: 'X1', range: [0, range] },
            yaxis: { title: 'X2', range: [0, range] },
            hovermode: 'closest',
            ...this.getThemeLayout() // <--- Aplicar tema actual
        };

        // @ts-ignore
        Plotly.react(this.elementId, traces, layout);
    },

    render3D(constraints: Constraint[], vertices: Point3D[], optimal: Point3D | null, range: number) {
        const traces: any[] = [];
        const isDark = document.documentElement.classList.contains('dark');

        // 1. Volumen 3D
        if (vertices.length >= 4) {
            traces.push({
                type: 'mesh3d',
                x: vertices.map(p => p.x),
                y: vertices.map(p => p.y),
                z: vertices.map(p => p.z),
                alphahull: -1,
                opacity: 0.3,
                color: isDark ? '#4ade80' : '#22c55e', // Verde ajustado
                name: 'Región Factible',
                hoverinfo: 'skip'
            });
        }

        this.addPointsTrace(traces, vertices, optimal, '3d');

        const layout = {
            title: 'Espacio 3D',
            scene: {
                xaxis: { title: 'X1', range: [0, range] },
                yaxis: { title: 'X2', range: [0, range] },
                zaxis: { title: 'X3', range: [0, range] },
                camera: { eye: { x: 1.6, y: 1.6, z: 1.6 } }
            },
            margin: { t: 30, l: 0, r: 0, b: 0 },
            ...this.getThemeLayout() // <--- Aplicar tema actual
        };

        // @ts-ignore
        Plotly.react(this.elementId, traces, layout);
    },

    addPointsTrace(traces: any[], vertices: Point3D[], optimal: Point3D | null, mode: '2d' | '3d') {
        const type = mode === '3d' ? 'scatter3d' : 'scatter';
        const isDark = document.documentElement.classList.contains('dark');

        // Puntos normales
        if (vertices.length > 0) {
            traces.push({
                type,
                x: vertices.map(p => p.x),
                y: vertices.map(p => p.y),
                ...(mode === '3d' ? { z: vertices.map(p => p.z) } : {}),
                mode: 'markers',
                name: 'Vértices',
                marker: {
                    color: isDark ? '#94a3b8' : '#64748b',
                    size: 4
                },
                hovertemplate: mode === '3d'
                    ? '(%{x:.2f}, %{y:.2f}, %{z:.2f})<extra></extra>'
                    : '(%{x:.2f}, %{y:.2f})<extra></extra>'
            });
        }

        // Punto Óptimo
        if (optimal) {
            traces.push({
                type,
                x: [optimal.x],
                y: [optimal.y],
                ...(mode === '3d' ? { z: [optimal.z] } : {}),
                mode: 'markers',
                name: 'ÓPTIMO',
                marker: {
                    color: '#ef4444', // Red-500
                    size: 10,
                    symbol: 'diamond',
                    line: { width: 2, color: isDark ? '#fff' : '#000' } // Borde para contraste
                },
                hovertemplate: `<b>ÓPTIMO</b><br>Z = ${optimal.valueZ?.toFixed(2)}<br>` +
                    (mode === '3d'
                        ? '(%{x:.2f}, %{y:.2f}, %{z:.2f})'
                        : '(%{x:.2f}, %{y:.2f})') +
                    '<extra></extra>'
            });
        }
    }
};