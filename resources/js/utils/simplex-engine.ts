import { create, all } from 'mathjs';

// Configurar Math.js para usar fracciones por defecto
const math = create(all, {
    number: 'Fraction' // Crucial: Todo cálculo será exacto (1/3, no 0.3333)
});

export interface SimplexConstraint {
    coefficients: Record<string, string>; // {'x1': '2', 'x2': '1'}
    operator: '<=' | '>=' | '=';
    rhs: string;
}

export interface Tableau {
    step: number;
    headers: string[];
    rows: TableauRow[];
    pivot?: { row: number; col: number; value: string };
    status: 'OPTIMAL' | 'UNBOUNDED' | 'IN_PROGRESS' | 'INFEASIBLE';
    zValue: string;
    baseVariables: string[];
}

export interface TableauRow {
    baseVar: string;
    values: string[]; // Representación string de la fracción
}

export class SimplexEngine {

    /**
     * Resuelve un problema de Maximización estándar usando el método Simplex Primal.
     * Asume que el problema entra en forma canónica o se puede estandarizar con holguras.
     */
    solve(
        objective: Record<string, string>,
        constraints: SimplexConstraint[]
    ): Tableau[] {

        const history: Tableau[] = [];

        // 1. Identificar variables (Decisión + Holgura)
        const decisionVars = Object.keys(objective).sort();
        const slackVars: string[] = [];

        // Generar nombres de holguras (S1, S2...)
        constraints.forEach((c, i) => {
            if (c.operator === '<=') {
                slackVars.push(`S${i + 1}`);
            }
            // Nota: Para >= o = se requerirían variables artificiales (Fase 2 / Gran M)
            // Aquí nos enfocamos en el Primal estándar (<-> Holguras positivas)
        });

        const allVars = [...decisionVars, ...slackVars];
        const headers = [...allVars, 'SOL'];

        // 2. Construir Tableau Inicial
        // Matriz A (Coeficientes) + Vector b (RHS)
        const rows: TableauRow[] = [];
        const baseVars: string[] = [...slackVars]; // Base inicial = Holguras

        constraints.forEach((c, i) => {
            const rowValues: any[] = [];

            // Variables de decisión
            decisionVars.forEach(dv => {
                rowValues.push(math.fraction(c.coefficients[dv] || 0));
            });

            // Variables de holgura (Matriz Identidad)
            slackVars.forEach((sv, j) => {
                rowValues.push(i === j ? math.fraction(1) : math.fraction(0));
            });

            // RHS
            rowValues.push(math.fraction(c.rhs));

            rows.push({
                baseVar: slackVars[i],
                values: rowValues
            });
        });

        // Fila Z (Cj - Zj)
        // En la tabla inicial, Zj = 0, así que la fila es -Cj (para maximizar Z - CX = 0)
        const zRowValues: any[] = [];
        decisionVars.forEach(dv => {
            const val = math.fraction(objective[dv] || 0);
            zRowValues.push(math.multiply(val, -1)); // Pasamos Cj al lado izquierdo: -Cj
        });
        // Holguras tienen costo 0
        slackVars.forEach(() => zRowValues.push(math.fraction(0)));
        // Valor Z inicial
        zRowValues.push(math.fraction(0));

        rows.push({ baseVar: 'Z', values: zRowValues });

        // Guardar estado inicial
        history.push(this.formatTableau(0, headers, rows, baseVars, 'IN_PROGRESS'));

        // 3. Iterar (Algoritmo Simplex)
        let iteration = 0;
        const MAX_ITERATIONS = 20; // Safety break

        while (iteration < MAX_ITERATIONS) {
            const currentRowState = history[history.length - 1];
            const zRow = rows[rows.length - 1].values;

            // A. Prueba de Optimalidad
            // ¿Hay algún coeficiente negativo en la fila Z? (Para max)
            // Buscamos el más negativo (regla de Dantzig)
            let minVal = math.fraction(0);
            let pivotColIdx = -1;

            // Iteramos solo sobre las variables (excluyendo SOL)
            for (let j = 0; j < allVars.length; j++) {
                const val = zRow[j];
                if (math.smaller(val, minVal)) {
                    minVal = val;
                    pivotColIdx = j;
                }
            }

            // Si no hay negativos, hemos terminado
            if (pivotColIdx === -1) {
                history[history.length - 1].status = 'OPTIMAL';
                break;
            }

            // B. Prueba del Cociente Mínimo (Ratio Test)
            // Theta = RHS / Coeficiente Pivot (solo si coef > 0)
            let minRatio = Infinity;
            let pivotRowIdx = -1;

            for (let i = 0; i < rows.length - 1; i++) { // Excluir fila Z
                const rhs = rows[i].values[rows[i].values.length - 1];
                const coef = rows[i].values[pivotColIdx];

                if (math.larger(coef, 0)) {
                    // @ts-ignore - mathjs types a veces fallan con number vs Fraction
                    const ratio = math.number(math.divide(rhs, coef));
                    if (ratio < minRatio) {
                        minRatio = ratio;
                        pivotRowIdx = i;
                    }
                }
            }

            // Si no hay ratio válido, el problema es no acotado
            if (pivotRowIdx === -1) {
                history[history.length - 1].status = 'UNBOUNDED';
                break;
            }

            // Guardar el pivote en la iteración actual antes de calcular la siguiente
            history[history.length - 1].pivot = {
                row: pivotRowIdx,
                col: pivotColIdx,
                value: this.fmt(rows[pivotRowIdx].values[pivotColIdx])
            };

            // C. Operaciones de Fila (Gauss-Jordan)
            const pivotElement = rows[pivotRowIdx].values[pivotColIdx];

            // 1. Normalizar fila pivote (hacer 1 el pivote)
            const newPivotRow = rows[pivotRowIdx].values.map(val => math.divide(val, pivotElement));
            rows[pivotRowIdx].values = newPivotRow;

            // Actualizar base: Sale variable vieja, entra variable nueva
            baseVars[pivotRowIdx] = allVars[pivotColIdx];
            rows[pivotRowIdx].baseVar = allVars[pivotColIdx];

            // 2. Hacer ceros en la columna pivote para las otras filas
            for (let i = 0; i < rows.length; i++) {
                if (i !== pivotRowIdx) {
                    const factor = rows[i].values[pivotColIdx]; // El valor que queremos eliminar
                    // R_i = R_i - (factor * R_pivote)
                    const newRow = rows[i].values.map((val, k) => {
                        return math.subtract(val, math.multiply(factor, newPivotRow[k]));
                    });
                    rows[i].values = newRow;
                }
            }

            iteration++;
            history.push(this.formatTableau(iteration, headers, rows, baseVars, 'IN_PROGRESS'));
        }

        return history;
    }

    // --- Helpers de Formateo ---

    private formatTableau(
        step: number,
        headers: string[],
        rows: any[],
        baseVars: string[],
        status: Tableau['status']
    ): Tableau {
        // Clonar estado para el historial (Deep copy visual)
        return {
            step,
            headers,
            baseVariables: [...baseVars],
            rows: rows.map(r => ({
                baseVar: r.baseVar,
                values: r.values.map((v: any) => this.fmt(v))
            })),
            status,
            // El valor de Z está en la última fila, última columna
            zValue: this.fmt(rows[rows.length - 1].values[rows[0].values.length - 1])
        };
    }

    private fmt(val: any): string {
        // Formatear fracción a string "3/2" o entero "5"
        return math.format(val, { fraction: 'ratio' });
    }
}

// Singleton export para uso directo
export const simplexEngine = new SimplexEngine();