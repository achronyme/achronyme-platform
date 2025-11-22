import { sensitivityEngine, SensitivityResult, ParametricPoint } from '../../utils/sensitivity-engine';
import { Tableau } from '../../utils/simplex-engine';

interface Variable {
    id: number;
    name: string;
    zCoef: string; // Coefficient in objective function
}

interface ConstraintRow {
    id: number;
    coefficients: Record<string, string>; // Map x1: '2', x2: '5'...
    operator: '<=' | '>=' | '=';
    rhs: string;
}

interface SensitivityState {
    isLoading: boolean;
    errorMessage: string | null;

    // Configuration
    objectiveType: 'max' | 'min';
    settings: string[]; // E.g., ['show_ranges', 'use_fractions', 'show_graph']
    analysisType: 'single' | 'multiple' | 'shadow_prices';

    // Mathematical Model
    variables: Variable[];
    constraints: ConstraintRow[];

    // Sensitivity Results
    result: SensitivityResult | null;
    currentTableau: Tableau | null;

    // Parametric Analysis
    selectedVariable: string | null;
    parameterValue: number;
    minParameterValue: number;
    maxParameterValue: number;
    parametricPoints: ParametricPoint[];

    // Methods
    init(): void;
    addVariable(): void;
    removeVariable(id: number): void;
    addConstraint(): void;
    removeConstraint(id: number): void;
    analyze(): void;
    updateParametricValue(value: number): void;
    selectVariableForAnalysis(varName: string): void;
    reset(): void;

    // Getters
    readonly showRanges: boolean;
    readonly useFractions: boolean;
    readonly showGraph: boolean;
    readonly currentZValue: string;
}

export function sensitivityState(): SensitivityState {
    return {
        isLoading: false,
        errorMessage: null,
        objectiveType: 'max',

        // Default settings
        settings: ['show_ranges', 'use_fractions', 'show_graph'],
        analysisType: 'single',

        // Getters for easy access
        get showRanges() { return this.settings.includes('show_ranges'); },
        get useFractions() { return this.settings.includes('use_fractions'); },
        get showGraph() { return this.settings.includes('show_graph'); },

        // Initialize with example problem
        // Max Z = 3x₁ + 5x₂
        // Subject to: x₁ ≤ 4, 2x₂ ≤ 12, 3x₁ + 2x₂ ≤ 18
        variables: [
            { id: 1, name: 'x1', zCoef: '3' },
            { id: 2, name: 'x2', zCoef: '5' }
        ],
        constraints: [
            {
                id: 1,
                coefficients: { 'x1': '1', 'x2': '0' },
                operator: '<=',
                rhs: '4'
            },
            {
                id: 2,
                coefficients: { 'x1': '0', 'x2': '2' },
                operator: '<=',
                rhs: '12'
            },
            {
                id: 3,
                coefficients: { 'x1': '3', 'x2': '2' },
                operator: '<=',
                rhs: '18'
            }
        ],

        result: null,
        currentTableau: null,

        // Parametric Analysis
        selectedVariable: null,
        parameterValue: 0,
        minParameterValue: 0,
        maxParameterValue: 10,
        parametricPoints: [],

        get currentZValue(): string {
            if (this.parametricPoints.length === 0) return '0';

            // Find the point closest to current parameter value
            const closest = this.parametricPoints.reduce((prev, curr) => {
                const prevDiff = Math.abs(parseFloat(prev.coefficientValue) - this.parameterValue);
                const currDiff = Math.abs(parseFloat(curr.coefficientValue) - this.parameterValue);
                return currDiff < prevDiff ? curr : prev;
            });

            return closest?.zValue || '0';
        },

        init() {
            console.log('Sensitivity Analysis Tool Initialized 🎯');
        },

        addVariable() {
            const newId = this.variables.length > 0
                ? Math.max(...this.variables.map(v => v.id)) + 1
                : 1;

            const newName = `x${newId}`;

            this.variables.push({
                id: newId,
                name: newName,
                zCoef: '0'
            });

            // Initialize coefficient 0 for this variable in all existing constraints
            this.constraints.forEach(c => {
                c.coefficients[newName] = '0';
            });
        },

        removeVariable(id) {
            if (this.variables.length <= 1) return; // Keep at least 1

            const varToRemove = this.variables.find(v => v.id === id);
            this.variables = this.variables.filter(v => v.id !== id);

            // Clean references in constraints
            if (varToRemove) {
                this.constraints.forEach(c => {
                    delete c.coefficients[varToRemove.name];
                });
            }

            // Reset selection if removed
            if (this.selectedVariable === varToRemove?.name) {
                this.selectedVariable = null;
            }
        },

        addConstraint() {
            const newId = Date.now();
            const coeffs: Record<string, string> = {};
            this.variables.forEach(v => {
                coeffs[v.name] = '0';
            });

            this.constraints.push({
                id: newId,
                coefficients: coeffs,
                operator: '<=',
                rhs: '0'
            });
        },

        removeConstraint(id) {
            if (this.constraints.length <= 1) return;
            this.constraints = this.constraints.filter(c => c.id !== id);
        },

        analyze() {
            this.isLoading = true;
            this.errorMessage = null;
            this.result = null;
            this.currentTableau = null;
            this.parametricPoints = [];

            setTimeout(() => {
                try {
                    // 1. Build objective function
                    const objective: Record<string, string> = {};
                    this.variables.forEach(v => {
                        objective[v.name] = v.zCoef;
                    });

                    // 2. Perform sensitivity analysis
                    const analysisResult = sensitivityEngine.analyze(
                        objective,
                        this.constraints,
                        this.selectedVariable || undefined,
                        this.selectedVariable ? {
                            min: this.minParameterValue,
                            max: this.maxParameterValue
                        } : undefined
                    );

                    if (!analysisResult.optimalTableau) {
                        throw new Error("No se pudo obtener el tableau óptimo.");
                    }

                    this.result = analysisResult;
                    this.currentTableau = analysisResult.optimalTableau;
                    this.parametricPoints = analysisResult.parametricAnalysis;

                    // Set parameter value to current coefficient if variable selected
                    if (this.selectedVariable) {
                        const currentVar = this.variables.find(v => v.name === this.selectedVariable);
                        if (currentVar) {
                            this.parameterValue = parseFloat(currentVar.zCoef);
                        }
                    }

                } catch (e: any) {
                    console.error(e);
                    this.errorMessage = e.message || "Error al realizar el análisis de sensibilidad.";
                } finally {
                    this.isLoading = false;
                }
            }, 100);
        },

        selectVariableForAnalysis(varName: string) {
            this.selectedVariable = varName;

            // Find the variable and set range
            const variable = this.variables.find(v => v.name === varName);
            if (variable) {
                const currentValue = parseFloat(variable.zCoef);
                this.parameterValue = currentValue;

                // Set reasonable range (±100% of current value, or ±10 if close to zero)
                const delta = Math.max(Math.abs(currentValue), 10);
                this.minParameterValue = Math.max(0, currentValue - delta);
                this.maxParameterValue = currentValue + delta;
            }
        },

        updateParametricValue(value: number) {
            this.parameterValue = value;
        },

        reset() {
            this.result = null;
            this.currentTableau = null;
            this.parametricPoints = [];
            this.selectedVariable = null;
        }

    } as unknown as SensitivityState;
}

// Register globally for Alpine
// @ts-ignore
window.sensitivityState = sensitivityState;
