import { create, all } from 'mathjs';
import { simplexEngine, Tableau, SimplexConstraint } from './simplex-engine';

// Configure Math.js to use fractions for exact calculations
const math = create(all, {
    number: 'Fraction'
});

export interface SensitivityRange {
    variable: string;
    currentValue: string;
    allowableIncrease: string;
    allowableDecrease: string;
    minValue: string;
    maxValue: string;
    isBasic: boolean;
}

export interface ShadowPrice {
    constraint: string;
    shadowPrice: string;
    currentRHS: string;
    allowableIncrease: string;
    allowableDecrease: string;
}

export interface ParametricPoint {
    coefficientValue: string;
    zValue: string;
    basis: string[];
}

export interface SensitivityResult {
    optimalTableau: Tableau;
    coefficientRanges: SensitivityRange[];
    shadowPrices: ShadowPrice[];
    parametricAnalysis: ParametricPoint[];
    hundredPercentRule: {
        isValid: boolean;
        totalPercentage: string;
    };
}

export class SensitivityEngine {

    /**
     * Performs complete sensitivity analysis on an optimization problem
     */
    analyze(
        objective: Record<string, string>,
        constraints: SimplexConstraint[],
        selectedVariable?: string,
        coefficientRange?: { min: number; max: number }
    ): SensitivityResult {

        // 1. Solve the problem to get optimal tableau
        const history = simplexEngine.solve(objective, constraints);
        if (history.length === 0) {
            throw new Error("No se pudo resolver el problema.");
        }

        const optimalTableau = history[history.length - 1];
        if (optimalTableau.status !== 'OPTIMAL') {
            throw new Error("El problema no tiene solución óptima.");
        }

        // 2. Calculate coefficient ranges
        const coefficientRanges = this.calculateCoefficientRanges(
            objective,
            optimalTableau,
            constraints
        );

        // 3. Calculate shadow prices
        const shadowPrices = this.calculateShadowPrices(
            optimalTableau,
            constraints
        );

        // 4. Perform parametric analysis if variable selected
        let parametricAnalysis: ParametricPoint[] = [];
        if (selectedVariable && coefficientRange) {
            parametricAnalysis = this.performParametricAnalysis(
                objective,
                constraints,
                selectedVariable,
                coefficientRange
            );
        }

        // 5. Check 100% rule (placeholder for now)
        const hundredPercentRule = {
            isValid: true,
            totalPercentage: '0'
        };

        return {
            optimalTableau,
            coefficientRanges,
            shadowPrices,
            parametricAnalysis,
            hundredPercentRule
        };
    }

    /**
     * Calculate allowable ranges for each objective function coefficient
     */
    private calculateCoefficientRanges(
        objective: Record<string, string>,
        optimalTableau: Tableau,
        constraints: SimplexConstraint[]
    ): SensitivityRange[] {

        const ranges: SensitivityRange[] = [];
        const decisionVars = Object.keys(objective).sort();
        const basicVars = optimalTableau.baseVariables.filter(v => v !== 'Z');

        // Get the Z row (last row in tableau)
        const zRow = optimalTableau.rows[optimalTableau.rows.length - 1];

        decisionVars.forEach((varName, varIndex) => {
            const currentValue = objective[varName];
            const isBasic = basicVars.includes(varName);

            if (isBasic) {
                // For basic variables, analyze the reduced costs
                const range = this.calculateBasicVariableRange(
                    varName,
                    varIndex,
                    currentValue,
                    optimalTableau,
                    decisionVars
                );
                ranges.push(range);
            } else {
                // For non-basic variables, check the reduced cost
                const range = this.calculateNonBasicVariableRange(
                    varName,
                    varIndex,
                    currentValue,
                    optimalTableau,
                    decisionVars
                );
                ranges.push(range);
            }
        });

        return ranges;
    }

    private calculateBasicVariableRange(
        varName: string,
        varIndex: number,
        currentValue: string,
        optimalTableau: Tableau,
        decisionVars: string[]
    ): SensitivityRange {

        const zRow = optimalTableau.rows[optimalTableau.rows.length - 1];
        let allowableIncrease = Infinity;
        let allowableDecrease = Infinity;

        // Find the row where this variable is basic
        const basicRowIndex = optimalTableau.rows.findIndex(
            (row, idx) => row.baseVar === varName && idx < optimalTableau.rows.length - 1
        );

        if (basicRowIndex !== -1) {
            const basicRow = optimalTableau.rows[basicRowIndex];

            // For each non-basic variable, calculate the limit
            decisionVars.forEach((nbVar, nbIndex) => {
                if (!optimalTableau.baseVariables.includes(nbVar)) {
                    const reducedCost = math.fraction(zRow.values[nbIndex]);
                    const coefficient = math.fraction(basicRow.values[nbIndex]);

                    if (!math.equal(coefficient, 0)) {
                        // Calculate the ratio
                        const ratio = math.number(math.divide(
                            math.abs(reducedCost),
                            math.abs(coefficient)
                        ));

                        // If coefficient > 0, it limits the decrease
                        // If coefficient < 0, it limits the increase
                        if (math.larger(coefficient, 0)) {
                            if (math.smaller(reducedCost, 0)) {
                                allowableDecrease = Math.min(allowableDecrease, ratio);
                            }
                        } else {
                            if (math.smaller(reducedCost, 0)) {
                                allowableIncrease = Math.min(allowableIncrease, ratio);
                            }
                        }
                    }
                }
            });
        }

        const current = math.fraction(currentValue);
        const minValue = allowableDecrease === Infinity
            ? '0'
            : this.fmt(math.subtract(current, allowableDecrease));
        const maxValue = allowableIncrease === Infinity
            ? '∞'
            : this.fmt(math.add(current, allowableIncrease));

        return {
            variable: varName,
            currentValue: this.fmt(current),
            allowableIncrease: allowableIncrease === Infinity ? '∞' : this.fmt(allowableIncrease),
            allowableDecrease: allowableDecrease === Infinity ? '∞' : this.fmt(allowableDecrease),
            minValue,
            maxValue,
            isBasic: true
        };
    }

    private calculateNonBasicVariableRange(
        varName: string,
        varIndex: number,
        currentValue: string,
        optimalTableau: Tableau,
        decisionVars: string[]
    ): SensitivityRange {

        const zRow = optimalTableau.rows[optimalTableau.rows.length - 1];
        const reducedCost = math.fraction(zRow.values[varIndex]);

        // For maximization, non-basic variables have reduced cost <= 0
        // The coefficient can increase by |reduced cost| before it becomes profitable
        const allowableIncrease = math.number(math.abs(reducedCost));

        // It can decrease indefinitely (to -∞) without affecting optimality
        const allowableDecrease = Infinity;

        const current = math.fraction(currentValue);
        const minValue = '−∞';
        const maxValue = this.fmt(math.add(current, allowableIncrease));

        return {
            variable: varName,
            currentValue: this.fmt(current),
            allowableIncrease: this.fmt(allowableIncrease),
            allowableDecrease: '∞',
            minValue,
            maxValue,
            isBasic: false
        };
    }

    /**
     * Calculate shadow prices (dual values) from the optimal tableau
     */
    private calculateShadowPrices(
        optimalTableau: Tableau,
        constraints: SimplexConstraint[]
    ): ShadowPrice[] {

        const shadowPrices: ShadowPrice[] = [];
        const zRow = optimalTableau.rows[optimalTableau.rows.length - 1];

        // Shadow prices are in the slack variable columns of the Z row
        constraints.forEach((constraint, index) => {
            const slackVar = `S${index + 1}`;
            const slackIndex = optimalTableau.headers.indexOf(slackVar);

            if (slackIndex !== -1 && slackIndex < zRow.values.length - 1) {
                const shadowPrice = math.fraction(zRow.values[slackIndex]);

                shadowPrices.push({
                    constraint: `R${index + 1}`,
                    shadowPrice: this.fmt(math.multiply(shadowPrice, -1)), // Negate for shadow price
                    currentRHS: constraint.rhs,
                    allowableIncrease: '∞', // Placeholder - would need right-hand side ranging
                    allowableDecrease: '∞'  // Placeholder
                });
            }
        });

        return shadowPrices;
    }

    /**
     * Perform parametric analysis by varying a coefficient
     */
    private performParametricAnalysis(
        objective: Record<string, string>,
        constraints: SimplexConstraint[],
        selectedVariable: string,
        range: { min: number; max: number }
    ): ParametricPoint[] {

        const points: ParametricPoint[] = [];
        const step = (range.max - range.min) / 20; // 20 points

        for (let i = 0; i <= 20; i++) {
            const newCoef = range.min + (step * i);
            const modifiedObjective = { ...objective };
            modifiedObjective[selectedVariable] = newCoef.toString();

            try {
                const history = simplexEngine.solve(modifiedObjective, constraints);
                if (history.length > 0) {
                    const result = history[history.length - 1];
                    if (result.status === 'OPTIMAL') {
                        points.push({
                            coefficientValue: this.fmt(newCoef),
                            zValue: result.zValue,
                            basis: result.baseVariables.filter(v => v !== 'Z')
                        });
                    }
                }
            } catch (e) {
                // Skip invalid points
            }
        }

        return points;
    }

    private fmt(val: any): string {
        // Format fraction to string "3/2" or integer "5"
        return math.format(val, { fraction: 'ratio' });
    }
}

// Singleton export for direct use
export const sensitivityEngine = new SensitivityEngine();
