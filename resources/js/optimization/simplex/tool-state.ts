import { simplexEngine, Tableau } from '../../utils/simplex-engine';

interface Variable {
    id: number;
    name: string;
    zCoef: string; // Coeficiente en función objetivo
}

interface ConstraintRow {
    id: number;
    coefficients: Record<string, string>; // Mapa x1: '2', x2: '5'...
    operator: '<=' | '>=' | '=';
    rhs: string;
}

interface SimplexState {
    isLoading: boolean;
    errorMessage: string | null;

    // Configuración (Refactorizado para checkbox-list)
    objectiveType: 'max' | 'min';
    settings: string[]; // Ej: ['show_steps', 'use_fractions']

    // Modelo Matemático
    variables: Variable[];
    constraints: ConstraintRow[];

    // Resultados
    history: Tableau[];
    currentStep: number;
    currentTableau: Tableau | null;

    // Métodos
    init(): void;
    addVariable(): void;
    removeVariable(id: number): void;
    addConstraint(): void;
    removeConstraint(id: number): void;
    solve(): void;
    nextStep(): void;
    prevStep(): void;
    reset(): void;

    // Getters helpers (opcionales, para uso interno si se requiere)
    readonly showSteps: boolean;
    readonly useFractions: boolean;
}

export function simplexState(): SimplexState {
    return {
        isLoading: false,
        errorMessage: null,
        objectiveType: 'max',

        // Inicializamos las opciones activas por defecto
        settings: ['show_steps', 'use_fractions'],

        // Getters para acceder a la config fácilmente desde JS
        get showSteps() { return this.settings.includes('show_steps'); },
        get useFractions() { return this.settings.includes('use_fractions'); },

        // Inicializamos con un problema ejemplo (2 variables, 2 restricciones)
        variables: [
            { id: 1, name: 'x1', zCoef: '30' },
            { id: 2, name: 'x2', zCoef: '20' }
        ],
        constraints: [
            {
                id: 1,
                coefficients: { 'x1': '2', 'x2': '1' },
                operator: '<=',
                rhs: '100'
            },
            {
                id: 2,
                coefficients: { 'x1': '1', 'x2': '3' },
                operator: '<=',
                rhs: '80'
            }
        ],

        history: [],
        currentStep: 0,
        currentTableau: null,

        init() {
            console.log('Simplex Tool Initialized 🚀');
        },

        addVariable() {
            // Generar ID: max(id) + 1
            const newId = this.variables.length > 0
                ? Math.max(...this.variables.map(v => v.id)) + 1
                : 1;

            const newName = `x${newId}`;

            this.variables.push({
                id: newId,
                name: newName,
                zCoef: '0'
            });

            // Inicializar coeficiente 0 para esta variable en todas las restricciones existentes
            this.constraints.forEach(c => {
                c.coefficients[newName] = '0';
            });
        },

        removeVariable(id) {
            if (this.variables.length <= 1) return; // Mantener al menos 1

            const varToRemove = this.variables.find(v => v.id === id);
            this.variables = this.variables.filter(v => v.id !== id);

            // Limpiar referencias en restricciones
            if (varToRemove) {
                this.constraints.forEach(c => {
                    delete c.coefficients[varToRemove.name];
                });
            }
        },

        addConstraint() {
            const newId = Date.now();
            // Pre-llenar coeficientes con 0 para todas las variables actuales
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

        solve() {
            this.isLoading = true;
            this.errorMessage = null;
            this.history = [];
            this.currentStep = 0;
            this.currentTableau = null;

            // Timeout para permitir que la UI muestre el estado de carga
            setTimeout(() => {
                try {
                    // 1. Construir Objetos para el Engine
                    const objective: Record<string, string> = {};
                    this.variables.forEach(v => {
                        objective[v.name] = v.zCoef;
                    });

                    // 2. Llamar al Motor
                    const result = simplexEngine.solve(objective, this.constraints);

                    if (result.length === 0) throw new Error("No se pudo generar la tabla inicial.");

                    this.history = result;
                    this.currentStep = 0;
                    this.currentTableau = this.history[0];

                } catch (e: any) {
                    console.error(e);
                    this.errorMessage = e.message || "Error al resolver el problema.";
                } finally {
                    this.isLoading = false;
                }
            }, 100);
        },

        nextStep() {
            if (this.currentStep < this.history.length - 1) {
                this.currentStep++;
                this.currentTableau = this.history[this.currentStep];
            }
        },

        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.currentTableau = this.history[this.currentStep];
            }
        },

        reset() {
            this.history = [];
            this.currentTableau = null;
            this.currentStep = 0;
        }

    } as unknown as SimplexState;
}

// Registrar globalmente para Alpine
// @ts-ignore
window.simplexState = simplexState;