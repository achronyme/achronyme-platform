import * as math from 'mathjs';

export interface Point3D { x: number; y: number; z: number; isValid: boolean; valueZ?: number; }
export interface Constraint { x1: number; x2: number; x3: number; operator: '<=' | '>=' | '='; rhs: number; }

export class GraphicalEngine {

    // --- Lógica 2D (Sin cambios mayores, solo limpieza de tipos) ---
    solve2D(objective: { x1: number, x2: number }, constraints: Constraint[], type: 'max' | 'min') {
        const vertices: Point3D[] = [];

        // Restricciones de no negatividad
        const allConstraints = [
            ...constraints,
            { x1: 1, x2: 0, x3: 0, operator: '>=', rhs: 0 }, // x1 >= 0
            { x1: 0, x2: 1, x3: 0, operator: '>=', rhs: 0 }  // x2 >= 0
        ] as Constraint[];

        // Intersección de PARES de líneas
        for (let i = 0; i < allConstraints.length; i++) {
            for (let j = i + 1; j < allConstraints.length; j++) {
                const c1 = allConstraints[i];
                const c2 = allConstraints[j];

                // Sistema 2x2: Ax = b
                const A = [[c1.x1, c1.x2], [c2.x1, c2.x2]];
                const b = [c1.rhs, c2.rhs];

                try {
                    if (Math.abs(math.det(A)) > 1e-10) {
                        const res = math.lusolve(A, b) as number[][];
                        const pt = { x: res[0][0], y: res[1][0], z: 0, isValid: true, valueZ: 0 };

                        if (this.isFeasible(pt, constraints)) {
                            pt.valueZ = (objective.x1 * pt.x) + (objective.x2 * pt.y);
                            vertices.push(pt);
                        }
                    }
                } catch (e) { /* Singular */ }
            }
        }
        return this.finalize(vertices, type);
    }

    // --- NUEVA Lógica 3D ---
    solve3D(objective: { x1: number, x2: number, x3: number }, constraints: Constraint[], type: 'max' | 'min') {
        const vertices: Point3D[] = [];

        // Restricciones extendidas (x, y, z >= 0)
        const allConstraints = [
            ...constraints,
            { x1: 1, x2: 0, x3: 0, operator: '>=', rhs: 0 }, // x >= 0
            { x1: 0, x2: 1, x3: 0, operator: '>=', rhs: 0 }, // y >= 0
            { x1: 0, x2: 0, x3: 1, operator: '>=', rhs: 0 }  // z >= 0
        ] as Constraint[];

        // Intersección de TRÍOS de planos (3 bucles anidados)
        const n = allConstraints.length;
        for (let i = 0; i < n; i++) {
            for (let j = i + 1; j < n; j++) {
                for (let k = j + 1; k < n; k++) {
                    const c1 = allConstraints[i];
                    const c2 = allConstraints[j];
                    const c3 = allConstraints[k];

                    // Sistema 3x3: Ax = b
                    const A = [
                        [c1.x1, c1.x2, c1.x3],
                        [c2.x1, c2.x2, c2.x3],
                        [c3.x1, c3.x2, c3.x3]
                    ];
                    const b = [c1.rhs, c2.rhs, c3.rhs];

                    try {
                        if (Math.abs(math.det(A)) > 1e-10) {
                            const res = math.lusolve(A, b) as number[][];
                            const pt = { x: res[0][0], y: res[1][0], z: res[2][0], isValid: true, valueZ: 0 };

                            if (this.isFeasible(pt, constraints)) {
                                pt.valueZ = (objective.x1 * pt.x) + (objective.x2 * pt.y) + (objective.x3 * pt.z);
                                vertices.push(pt);
                            }
                        }
                    } catch (e) { /* Singular */ }
                }
            }
        }
        return this.finalize(vertices, type);
    }

    // --- Helpers Comunes ---

    private isFeasible(pt: Point3D, constraints: Constraint[]): boolean {
        const epsilon = 1e-7; // Tolerancia flotante
        // 1. No negatividad básica
        if (pt.x < -epsilon || pt.y < -epsilon || pt.z < -epsilon) return false;

        // 2. Cumplimiento de restricciones de usuario
        return constraints.every(c => {
            const val = (c.x1 * pt.x) + (c.x2 * pt.y) + (c.x3 * pt.z);
            if (c.operator === '<=') return val <= c.rhs + epsilon;
            if (c.operator === '>=') return val >= c.rhs - epsilon;
            if (c.operator === '=') return Math.abs(val - c.rhs) < epsilon;
            return true;
        });
    }

    private finalize(vertices: Point3D[], type: 'max' | 'min') {
        const uniqueVertices = this.removeDuplicates(vertices);

        // Ordenar para encontrar óptimo
        uniqueVertices.sort((a, b) => type === 'max' ? (b.valueZ! - a.valueZ!) : (a.valueZ! - b.valueZ!));

        return {
            vertices: uniqueVertices,
            optimalPoint: uniqueVertices.length > 0 ? uniqueVertices[0] : null,
            optimalValue: uniqueVertices.length > 0 ? uniqueVertices[0].valueZ : 0
        };
    }

    private removeDuplicates(points: Point3D[]): Point3D[] {
        const unique: Point3D[] = [];
        points.forEach(p => {
            // Verificar si ya existe un punto cercano (distancia euclidiana pequeña)
            if (!unique.some(u =>
                Math.abs(u.x - p.x) < 1e-5 &&
                Math.abs(u.y - p.y) < 1e-5 &&
                Math.abs(u.z - p.z) < 1e-5
            )) {
                unique.push(p);
            }
        });
        return unique;
    }
}