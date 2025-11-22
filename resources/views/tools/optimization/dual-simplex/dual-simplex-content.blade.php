<div x-data="dualSimplexState()" x-init="init()">
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
                                'title' => 'Maximizar (Dual Simplex)',
                                'description' => 'Itera restaurando factibilidad primal desde dual factibilidad.'
                            ],
                            [
                                'value' => 'min',
                                'title' => 'Minimizar (Dual Simplex)',
                                'description' => 'Convierte a Max(-Z) y aplica el criterio dual.'
                            ]
                        ]"
                        x-model="objectiveType"
                    />
                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 text-xs text-slate-600 dark:text-slate-400">
                        <p class="font-bold text-blue-700 dark:text-blue-300 mb-1">Nota Técnica:</p>
                        <p>El <strong>Dual Simplex</strong> resuelve problemas con <em>dual factibilidad inicial</em> pero <em>primal infactibilidad</em> (algunos $b_i < 0$). Útil para análisis de sensibilidad y corregir bases después de agregar restricciones. Para problemas con $b_i \ge 0$, usa el <strong>Simplex Primal</strong>.</p>
                    </div>
                </div>

                {{-- Configuración Visual con Checkbox List --}}
                <div class="p-4 rounded-lg border bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 mb-2 text-sm">Visualización</h4>
                    <x-app-ui.checkbox-list
                        legend="Opciones de visualización"
                        name="dual_simplex_settings"
                        x-model="settings"
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

        {{-- 🔵 Centro: Tabla Dual Simplex --}}
        <div class="mb-8 h-full">
            <template x-if="!currentTableau">
                <div class="flex flex-col items-center justify-center h-full min-h-[400px] bg-slate-50 dark:bg-slate-800/50 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-700 text-slate-400">
                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="text-lg font-medium">Dual Simplex</p>
                    <p class="text-sm">Ingresa coeficientes y restricciones para comenzar.</p>
                </div>
            </template>

            {{-- Tabla con condición de visibilidad --}}
            <template x-if="currentTableau">
                <div x-show="settings.includes('show_steps')" class="bg-white dark:bg-slate-800 p-1 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden animate-fade-in">

                    {{-- Header de Navegación --}}
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
                        <span class="px-3 py-1 text-xs font-bold rounded-full border"
                            :class="{
                                'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400': currentTableau.status === 'OPTIMAL',
                                'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400': currentTableau.status === 'IN_PROGRESS',
                                'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400': currentTableau.status === 'UNBOUNDED' || currentTableau.status === 'INFEASIBLE'
                            }"
                            x-text="currentTableau.status === 'IN_PROGRESS' ? 'EN PROCESO' : (currentTableau.status === 'OPTIMAL' ? 'ÓPTIMO' : (currentTableau.status === 'INFEASIBLE' ? 'INFACTIBLE' : 'NO ACOTADO'))">
                        </span>
                    </div>

                    {{-- Contenido de la Tabla --}}
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

        {{-- 🔴 Derecha: Inputs --}}
        <x-slot:rightSidebar>
            @include('tools.optimization.dual-simplex.partials.inputs')
        </x-slot>
    </x-three-column-tool>
</div>
