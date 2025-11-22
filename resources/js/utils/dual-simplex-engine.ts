import { create, all } from 'mathjs';

// Configurar Math.js para usar fracciones por defecto
const math = create(all, {
    number: 'Fraction' // Crucial: Todo cálculo será exacto (1/3, no 0.3333)
});

export interface DualSimplexConstraint {
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

export class DualSimplexEngine {

    /**
     * Resuelve un problema usando el método Dual Simplex.
     * Este método es útil cuando tenemos dual factibilidad pero primal infactibilidad inicial.
     * Es decir, cuando los coeficientes en la fila Z son no-negativos pero algunos b_i < 0.
     */
    solve(
        objective: Record<string, string>,
        constraints: DualSimplexConstraint[],
        objectiveType: 'max' | 'min' = 'max'
    ): Tableau[] {

        const history: Tableau[] = [];

        // Si es minimización, convertimos a max(-Z)
        const isMinimization = objectiveType === 'min';
        const actualObjective = isMinimization
            ? Object.fromEntries(Object.entries(objective).map(([k, v]) => [k, math.format(math.multiply(math.fraction(v), -1), { fraction: 'ratio' })]))
            : objective;

        // 1. Identificar variables (Decisión + Holgura/Exceso)
        const decisionVars = Object.keys(actualObjective).sort();
        const slackVars: string[] = [];
        const surplusVars: string[] = [];

        // Generar nombres de variables de holgura/exceso según el operador
        constraints.forEach((c, i) => {
            if (c.operator === '<=') {
                slackVars.push(`S${i + 1}`);
            } else if (c.operator === '>=') {
                surplusVars.push(`E${i + 1}`); // Exceso (excess)
            }
            // Para '=' necesitaríamos variables artificiales, pero para el dual simplex
            // típicamente trabajamos con >= transformadas a forma estándar
        });

        const allVars = [...decisionVars, ...slackVars, ...surplusVars];
        const headers = [...allVars, 'SOL'];

        // 2. Construir Tableau Inicial
        const rows: TableauRow[] = [];
        const baseVars: string[] = [];

        let slackIdx = 0;
        let surplusIdx = 0;

        constraints.forEach((c, i) => {
            const rowValues: any[] = [];

            // Variables de decisión
            // Si la restricción es >=, multiplicamos los coeficientes por -1 para mantener consistencia
            decisionVars.forEach(dv => {
                let coef = math.fraction(c.coefficients[dv] || 0);
                if (c.operator === '>=') {
                    coef = math.multiply(coef, -1);
                }
                rowValues.push(coef);
            });

            // Variables de holgura (para <=)
            slackVars.forEach((sv, j) => {
                if (c.operator === '<=' && slackIdx === j) {
                    rowValues.push(math.fraction(1));
                } else {
                    rowValues.push(math.fraction(0));
                }
            });

            // Variables de exceso (para >=)
            surplusVars.forEach((ev, j) => {
                if (c.operator === '>=' && surplusIdx === j) {
                    // Como multiplicamos la restricción por -1, el coeficiente de exceso es 1
                    rowValues.push(math.fraction(1));
                } else {
                    rowValues.push(math.fraction(0));
                }
            });

            // RHS - Para restricciones >=, multiplicamos por -1 para obtener forma estándar
            // Esto hace que el problema sea dual factible pero primal infactible inicialmente
            let rhsValue = math.fraction(c.rhs);
            if (c.operator === '>=') {
                rhsValue = math.multiply(rhsValue, -1); // Negativo para infactibilidad primal inicial
            }
            rowValues.push(rhsValue);

            // La variable base inicial depende del operador
            let baseVar: string;
            if (c.operator === '<=') {
                baseVar = slackVars[slackIdx];
                slackIdx++;
            } else if (c.operator === '>=') {
                baseVar = surplusVars[surplusIdx];
                surplusIdx++;
            } else {
                // Para '=' necesitaríamos artificial
                baseVar = `A${i + 1}`;
            }

            baseVars.push(baseVar);
            rows.push({
                baseVar,
                values: rowValues
            });
        });

        // Fila Z (Cj - Zj)
        // Para el Dual Simplex, necesitamos calcular los costos reducidos correctamente
        // Los coeficientes iniciales deben ser no-negativos para dual factibilidad
        const zRowValues: any[] = [];

        // Para las variables de decisión
        decisionVars.forEach(dv => {
            const cj = math.fraction(actualObjective[dv] || 0);
            // En el Dual Simplex para minimización con restricciones >=,
            // comenzamos con los coeficientes originales negados
            zRowValues.push(math.multiply(cj, -1));
        });

        // Holguras y excesos tienen costo 0
        [...slackVars, ...surplusVars].forEach(() => zRowValues.push(math.fraction(0)));

        // Calcular el valor inicial de Z
        // En Dual Simplex, debemos calcular Z = sum(Cb * b) donde Cb son los costos de las variables básicas
        let zInitial = math.fraction(0);
        baseVars.forEach((bv, idx) => {
            // Solo las variables de decisión tienen costo no cero
            if (decisionVars.includes(bv)) {
                const cost = math.fraction(actualObjective[bv] || 0);
                const rhsValue = rows[idx].values[rows[idx].values.length - 1];
                zInitial = math.add(zInitial, math.multiply(cost, rhsValue));
            }
            // Variables de holgura/exceso tienen costo 0
        });

        // Si el problema original era minimización y lo convertimos, el valor se mantiene
        // Si es maximización, el valor ya está correcto
        zRowValues.push(isMinimization ? math.multiply(zInitial, -1) : zInitial);

        rows.push({ baseVar: 'Z', values: zRowValues });

        // Guardar estado inicial
        history.push(this.formatTableau(0, headers, rows, baseVars, 'IN_PROGRESS', isMinimization));

        // 3. Iterar (Algoritmo Dual Simplex)
        let iteration = 0;
        const MAX_ITERATIONS = 20; // Safety break

        while (iteration < MAX_ITERATIONS) {
            // A. Prueba de Optimalidad Primal
            // ¿Todas las variables en la columna SOL son >= 0?
            let allFeasible = true;
            let pivotRowIdx = -1;
            let mostNegativeRHS = math.fraction(0);

            for (let i = 0; i < rows.length - 1; i++) { // Excluir fila Z
                const rhs = rows[i].values[rows[i].values.length - 1];
                if (math.smaller(rhs, 0)) {
                    allFeasible = false;
                    // Seleccionar la fila con el RHS más negativo (variable saliente)
                    if (math.smaller(rhs, mostNegativeRHS)) {
                        mostNegativeRHS = rhs;
                        pivotRowIdx = i;
                    }
                }
            }

            // Si todos los RHS son no-negativos, hemos alcanzado factibilidad primal (ÓPTIMO)
            if (allFeasible) {
                history[history.length - 1].status = 'OPTIMAL';
                // Recalcular el valor Z correctamente para el estado final
                let zFinal = rows[rows.length - 1].values[rows[rows.length - 1].values.length - 1];
                if (isMinimization) {
                    zFinal = math.multiply(zFinal, -1);
                }
                history[history.length - 1].zValue = this.fmt(zFinal);
                break;
            }

            // B. Prueba del Cociente Mínimo (Ratio Test Dual)
            // Para la fila pivote, buscamos la columna que minimiza |Zj / aij|
            // donde aij < 0 (solo coeficientes negativos en la fila pivote)
            const zRow = rows[rows.length - 1].values;
            let minRatio = Infinity;
            let pivotColIdx = -1;

            for (let j = 0; j < allVars.length; j++) {
                const aij = rows[pivotRowIdx].values[j];
                const zj = zRow[j];

                // Solo consideramos coeficientes negativos en la fila pivote
                if (math.smaller(aij, 0)) {
                    // Ratio = |Zj / aij| = -Zj / aij (porque aij < 0)
                    // @ts-ignore
                    const ratio = math.number(math.divide(math.multiply(zj, -1), aij));
                    if (ratio < minRatio) {
                        minRatio = ratio;
                        pivotColIdx = j;
                    }
                }
            }

            // Si no hay ratio válido, el problema dual es no acotado (primal infactible)
            if (pivotColIdx === -1) {
                history[history.length - 1].status = 'INFEASIBLE';
                break;
            }

            // Guardar el pivote en la iteración actual
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
                    const factor = rows[i].values[pivotColIdx];
                    // R_i = R_i - (factor * R_pivote)
                    const newRow = rows[i].values.map((val, k) => {
                        return math.subtract(val, math.multiply(factor, newPivotRow[k]));
                    });
                    rows[i].values = newRow;
                }
            }

            iteration++;
            history.push(this.formatTableau(iteration, headers, rows, baseVars, 'IN_PROGRESS', isMinimization));
        }

        // Si llegamos al máximo de iteraciones sin terminar
        if (iteration >= MAX_ITERATIONS && history[history.length - 1].status === 'IN_PROGRESS') {
            history[history.length - 1].status = 'INFEASIBLE';
        }

        return history;
    }

    // --- Helpers de Formateo ---

    private formatTableau(
        step: number,
        headers: string[],
        rows: any[],
        baseVars: string[],
        status: Tableau['status'],
        isMinimization: boolean = false
    ): Tableau {
        // Obtener el valor Z de la última fila, última columna
        let zValue = rows[rows.length - 1].values[rows[rows.length - 1].values.length - 1];

        // Si el problema original era minimización y convertimos a Max(-Z),
        // necesitamos negar el valor para mostrar el Z original correcto
        if (isMinimization) {
            zValue = math.multiply(zValue, -1);
        }

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
            zValue: this.fmt(zValue)
        };
    }

    private fmt(val: any): string {
        // Formatear fracción a string "3/2" o entero "5"
        return math.format(val, { fraction: 'ratio' });
    }
}

// Singleton export para uso directo
export const dualSimplexEngine = new DualSimplexEngine();
