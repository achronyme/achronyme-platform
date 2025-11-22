<div x-data="simplexState()" x-init="init()">
    <x-three-column-tool>
        {{-- 🟢 Izquierda: Configuración --}}
        <x-slot:leftSidebar>
            <div class="space-y-6">
                
                {{-- Selector de Objetivo --}}
                <div class="p-4 rounded-lg bg-white border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 mb-3 text-sm">Objetivo & Algoritmo</h4>
                    <x-app-ui.radio-list
                        legend=""
                        name="objective_type"
                        :options="[
                            [
                                'value' => 'max', 
                                'title' => 'Maximizar (Simplex Primal)', 
                                'description' => 'Itera buscando optimalidad partiendo de una base factible.'
                            ],
                            [
                                'value' => 'min', 
                                'title' => 'Minimizar (Simplex Primal)', 
                                'description' => 'Convierte a Max(-Z) para aplicar el criterio estándar.'
                            ]
                        ]"
                        x-model="objectiveType"
                    />
                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 text-xs text-slate-600 dark:text-slate-400">
                        <p class="font-bold text-blue-700 dark:text-blue-300 mb-1">Nota Técnica:</p>
                        <p>Este módulo requiere $b_i \ge 0$. Para corregir infactibilidad inicial, utiliza el módulo <strong>Dual Simplex</strong>.</p>
                    </div>
                </div>

                {{-- Configuración Visual con CHECKBOX-LIST --}}
                <div class="p-4 rounded-lg border bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 mb-2 text-sm">Visualización</h4>
                    
                    <x-app-ui.checkbox-list
                        legend="Opciones de visualización"
                        name="simplex_settings"
                        x-model="settings" {{-- Enlaza al array en tool-state.ts --}}
                        :options="[
                            [
                                'title' => 'Mostrar paso a paso', 
                                'value' => 'show_steps', 
                                'description' => 'Ver las tablas de cada iteración.',
                                'name' => 'show_steps'
                            ],
                            [
                                'title' => 'Usar Fracciones', 
                                'value' => 'use_fractions', 
                                'description' => 'Cálculos exactos (ej. 3/2) en lugar de decimales.',
                                'name' => 'use_fractions'
                            ]
                        ]"
                        :checkedValues="['show_steps', 'use_fractions']"
                    />
                </div>

                {{-- Card de Estado --}}
                <div class="p-4 rounded-lg bg-indigo-50 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-800 transition-colors">
                    <h4 class="font-bold text-indigo-800 dark:text-indigo-400 mb-1 flex items-center gap-2">
                        <span class="text-xl">🚀</span> Estado Actual
                    </h4>
                    <div class="flex justify-between items-end mt-2 pt-2 border-t border-indigo-200 dark:border-indigo-800">
                        <span class="text-sm text-indigo-700 dark:text-indigo-300">Z Óptimo:</span>
                        <span class="font-mono font-bold text-lg text-indigo-900 dark:text-white" 
                              x-text="currentTableau ? currentTableau.zValue : '0'"></span>
                    </div>
                </div>
            </div>
        </x-slot>

        {{-- 🔵 Centro: Tabla Simplex --}}
        <div class="mb-8 h-full">
            <template x-if="!currentTableau">
                <div class="flex flex-col items-center justify-center h-full min-h-[400px] bg-slate-50 dark:bg-slate-800/50 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-700 text-slate-400">
                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="text-lg font-medium">Simplex Primal</p>
                    <p class="text-sm">Ingresa coeficientes y restricciones para comenzar.</p>
                </div>
            </template>

            {{-- Tabla con condición de visibilidad --}}
            <template x-if="currentTableau">
                {{-- Verificamos si 'show_steps' está en el array settings --}}
                <div x-show="settings.includes('show_steps')" class="bg-white dark:bg-slate-800 p-1 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden animate-fade-in">
                    
                    {{-- ... (El resto de la tabla y controles sigue igual) ... --}}
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <button @click="prevStep()" :disabled="currentStep === 0" class="p-1.5 rounded hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-30 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            
                            <div class="text-center min-w-[120px]">
                                <h3 class="font-bold text-slate-700 dark:text-slate-300 text-lg" x-text="'Iteración ' + currentTableau.step"></h3>
                                <span class="text-xs font-mono text-slate-500 dark:text-slate-400" x-text="currentTableau.status"></span>
                            </div>

                            <button @click="nextStep()" :disabled="currentStep === history.length - 1" class="p-1.5 rounded hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-30 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                        {{-- Badge de estado --}}
                        <span class="px-3 py-1 text-xs font-bold rounded-full border"
                            :class="{
                                'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400': currentTableau.status === 'OPTIMAL',
                                'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400': currentTableau.status === 'IN_PROGRESS',
                                'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400': currentTableau.status === 'UNBOUNDED' || currentTableau.status === 'INFEASIBLE'
                            }"
                            x-text="currentTableau.status === 'IN_PROGRESS' ? 'EN PROCESO' : (currentTableau.status === 'OPTIMAL' ? 'ÓPTIMO' : 'NO ACOTADO')">
                        </span>
                    </div>
                    
                    {{-- Tabla de datos --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                            <thead class="text-xs text-slate-700 uppercase bg-slate-100 dark:bg-slate-700 dark:text-slate-400">
                                <tr>
                                    <th class="px-4 py-3 w-20 border-r dark:border-slate-600">Base</th>
                                    <template x-for="header in currentTableau.headers.slice(0, -1)" :key="header">
                                        <th class="px-4 py-3 text-center border-r border-slate-100 dark:border-slate-700 last:border-0" x-text="header"></th>
                                    </template>
                                    <th class="px-4 py-3 text-right bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-white font-bold border-l dark:border-slate-500 w-24">SOL</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <template x-for="(row, rIndex) in currentTableau.rows" :key="rIndex">
                                    <tr class="bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                        :class="{'bg-indigo-50/50 dark:bg-indigo-900/10 border-t-2 border-indigo-100 dark:border-indigo-800': row.baseVar === 'Z'}">
                                        
                                        <th class="px-4 py-3 font-bold text-slate-900 whitespace-nowrap dark:text-white bg-slate-50 dark:bg-slate-800/50 border-r dark:border-slate-600" 
                                            x-text="row.baseVar">
                                        </th>

                                        <template x-for="(val, cIndex) in row.values.slice(0, -1)" :key="cIndex">
                                            <td class="px-4 py-3 text-center font-mono border-r border-slate-100 dark:border-slate-700/50" 
                                                x-text="val"
                                                :class="{
                                                    'text-indigo-600 font-bold bg-indigo-50 dark:bg-indigo-900/30 ring-1 ring-indigo-200 dark:ring-indigo-700': 
                                                        currentTableau.pivot && rIndex === currentTableau.pivot.row && cIndex === currentTableau.pivot.col
                                                }">
                                            </td>
                                        </template>

                                        <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-800/50 border-l dark:border-slate-600"
                                            x-text="row.values[row.values.length - 1]">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-2 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 text-xs text-center text-slate-400 flex justify-center gap-4">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 bg-indigo-500 rounded-full"></span> Pivote</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 bg-slate-300 dark:bg-slate-600 rounded-full"></span> Solución</span>
                    </div>
                </div>
            </template>
        </div>

        {{-- 🔴 Derecha: Inputs (Sin cambios, se mantiene igual que la versión anterior) --}}
        <x-slot:rightSidebar>
            {{-- (Aquí va el mismo bloque de inputs dinámicos que ya aprobaste) --}}
            @include('tools.optimization.simplex.partials.inputs') {{-- (O el código directo si no usas partials) --}}
            <div class="space-y-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/50 rounded-md text-indigo-600 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="font-medium text-slate-900 dark:text-white">Definición del Problema</h3>
                    </div>
                    
                    <div class="space-y-6">
                        {{-- A. Función Objetivo --}}
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-inner">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Coeficientes de Z</h4>
                                <div class="flex gap-2">
                                    <button @click="removeVariable(variables[variables.length-1].id)" 
                                            class="text-xs text-red-500 hover:text-red-700 disabled:opacity-30 transition-colors font-medium"
                                            :disabled="variables.length <= 1">
                                        - Var
                                    </button>
                                    <button @click="addVariable()" class="text-xs text-indigo-600 hover:text-indigo-500 font-bold transition-colors">
                                        + Variable
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <template x-for="v in variables" :key="v.id">
                                    <div>
                                        <x-app-ui.input-addon label="" x-addon="'x' + v.id" x-model="v.zCoef" placeholder="0" />
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- B. Restricciones --}}
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Sujeto a:</h4>
                                <x-app-ui.secondary-button type="button" @click="addConstraint()" class="!py-1 !px-2 text-xs">
                                    + Restricción
                                </x-app-ui.secondary-button>
                            </div>
                            <div class="space-y-3">
                                <template x-for="(c, index) in constraints" :key="c.id">
                                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-xs font-mono text-slate-400" x-text="'Restricción ' + (index + 1)"></span>
                                            <button @click="removeConstraint(c.id)" class="text-slate-300 hover:text-red-500 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-3">
                                            <template x-for="v in variables" :key="v.id">
                                                <div>
                                                    <x-app-ui.input-addon label="" x-addon="'x' + v.id" x-model="c.coefficients[v.name]" placeholder="0" />
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex gap-2 items-center border-t border-slate-100 dark:border-slate-700 pt-2">
                                            <select x-model="c.operator" class="block w-16 rounded border-0 py-1 text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 text-xs font-bold text-center cursor-pointer">
                                                <option value="<=">≤</option>
                                                <option value=">=">≥</option>
                                                <option value="=">=</option>
                                            </select>
                                            <div class="flex-1">
                                                <x-app-ui.input-text label="" name="rhs" x-model="c.rhs" placeholder="SOL" class="!py-1 !text-xs font-bold" />
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <x-app-ui.button @click="solve()" loading-text="Optimizando..." class="w-full flex items-center justify-center gap-2 py-3 text-lg shadow-lg shadow-indigo-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span>Ejecutar Simplex</span>
                    </x-app-ui.button>
                </div>
            </div>
        </x-slot>
    </x-three-column-tool>
</div>