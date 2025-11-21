import { GraphicalEngine, Constraint } from './graphical-engine';
import { PlotlyAdapter } from './plotly-adapter'; // Asegúrate que el nombre del archivo sea correcto (plotly vs ploty)

interface GraphicalState {
    isLoading: boolean;
    objectiveType: 'max' | 'min';
    axisRange: number;
    numVariables: number; // 2 o 3

    // Inputs (Strings para permitir escritura fácil)
    objX1: string; objX2: string; objX3: string;
    constraints: { x1: string; x2: string; x3: string; operator: string; rhs: string; id: number }[];

    // Resultado
    optimalValue: number | null;
    optimalPoint: { x: number, y: number, z: number } | null;

    // Métodos
    init(): void;
    addConstraint(): void;
    removeConstraint(id: number): void;
    calculate(): void;
}

export function graphicalState(): GraphicalState {
    return {
        isLoading: false,
        objectiveType: 'max',
        axisRange: 50,
        numVariables: 2, // Default 2D

        objX1: '30', objX2: '20', objX3: '0',

        constraints: [
            { x1: '2', x2: '1', x3: '0', operator: '<=', rhs: '100', id: 1 },
            { x1: '1', x2: '1', x3: '0', operator: '<=', rhs: '80', id: 2 }
        ],

        optimalValue: null,
        optimalPoint: null,

        init() {
            PlotlyAdapter.init();

            // Recalcular si cambia el rango del slider visual
            this.$watch('axisRange', () => {
                if (this.optimalPoint) this.calculate(); // Solo recalcular si ya había resultado
                else PlotlyAdapter.init(); // O solo redibujar ejes
            });

            // Resetear gráfica al cambiar dimensión
            this.$watch('numVariables', () => {
                this.optimalPoint = null;
                this.optimalValue = null;
                PlotlyAdapter.init();
            });
        },

        addConstraint() {
            this.constraints.push({
                x1: '0', x2: '0', x3: '0',
                operator: '<=', rhs: '0', id: Date.now()
            });
        },

        removeConstraint(id) {
            this.constraints = this.constraints.filter(c => c.id !== id);
        },

        calculate() {
            this.isLoading = true;

            // Pequeño timeout para permitir que la UI muestre el loader
            setTimeout(() => {
                try {
                    const engine = new GraphicalEngine();

                    // 1. Parsear Inputs
                    const parsedConstraints = this.constraints.map(c => ({
                        x1: parseFloat(c.x1) || 0,
                        x2: parseFloat(c.x2) || 0,
                        x3: parseFloat(c.x3) || 0,
                        operator: c.operator as any,
                        rhs: parseFloat(c.rhs) || 0
                    }));

                    // 2. Bifurcación 2D vs 3D
                    let result;

                    if (this.numVariables === 3) {
                        const objective = {
                            x1: parseFloat(this.objX1) || 0,
                            x2: parseFloat(this.objX2) || 0,
                            x3: parseFloat(this.objX3) || 0
                        };

                        result = engine.solve3D(objective, parsedConstraints, this.objectiveType);

                        this.optimalValue = result.optimalValue || 0;
                        this.optimalPoint = result.optimalPoint;

                        PlotlyAdapter.render3D(parsedConstraints, result.vertices, result.optimalPoint, this.axisRange);

                    } else {
                        // Modo 2D
                        const objective = {
                            x1: parseFloat(this.objX1) || 0,
                            x2: parseFloat(this.objX2) || 0
                        };

                        result = engine.solve2D(objective, parsedConstraints, this.objectiveType);

                        this.optimalValue = result.optimalValue || 0;
                        this.optimalPoint = result.optimalPoint;

                        PlotlyAdapter.render2D(parsedConstraints, result.vertices, result.optimalPoint, this.axisRange);
                    }

                } catch (error) {
                    console.error("Error de cálculo:", error);
                    // Aquí podrías setear this.errorMessage
                } finally {
                    this.isLoading = false;
                }
            }, 50);
        }
    } as unknown as GraphicalState;
}

// Exponer a window para Alpine
// @ts-ignore
window.graphicalState = graphicalState;