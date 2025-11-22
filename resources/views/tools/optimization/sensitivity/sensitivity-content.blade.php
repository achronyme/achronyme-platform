<div x-data="sensitivityState()" x-init="init()">
    <x-three-column-tool>
        {{-- 🟢 Left: Configuration --}}
        <x-slot:leftSidebar>
            <div class="space-y-6">
                {{-- Objective Type Selector --}}
                <div class="p-4 rounded-lg bg-white border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 mb-3 text-sm">Objetivo & Análisis</h4>
                    <x-app-ui.radio-list
                        legend=""
                        name="objective_type"
                        :options="[
                            [
                                'value' => 'max',
                                'title' => 'Maximizar',
                                'description' => 'Análisis de sensibilidad para problemas de maximización.'
                            ],
                            [
                                'value' => 'min',
                                'title' => 'Minimizar',
                                'description' => 'Análisis de sensibilidad para problemas de minimización.'
                            ]
                        ]"
                        x-model="objectiveType"
                    />
                </div>

                {{-- Analysis Type --}}
                <div class="p-4 rounded-lg border bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 mb-3 text-sm">Tipo de Análisis</h4>
                    <x-app-ui.radio-list
                        legend=""
                        name="analysis_type"
                        :options="[
                            [
                                'value' => 'single',
                                'title' => 'Coeficiente Individual',
                                'description' => 'Analizar rangos de un coeficiente a la vez.'
                            ],
                            [
                                'value' => 'multiple',
                                'title' => 'Múltiples Coeficientes',
                                'description' => 'Regla del 100% para cambios simultáneos.'
                            ],
                            [
                                'value' => 'shadow_prices',
                                'title' => 'Precios Sombra',
                                'description' => 'Analizar el valor marginal de los recursos.'
                            ]
                        ]"
                        x-model="analysisType"
                    />
                </div>

                {{-- Visualization Settings --}}
                <div class="p-4 rounded-lg border bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 mb-2 text-sm">Visualización</h4>
                    <x-app-ui.checkbox-list
                        legend="Opciones de visualización"
                        name="sensitivity_settings"
                        x-model="settings"
                        :options="[
                            [
                                'title' => 'Mostrar Rangos',
                                'value' => 'show_ranges',
                                'description' => 'Ver rangos permitidos para cada coeficiente.',
                                'name' => 'show_ranges'
                            ],
                            [
                                'title' => 'Usar Fracciones',
                                'value' => 'use_fractions',
                                'description' => 'Cálculos exactos (ej. 3/2) en lugar de decimales.',
                                'name' => 'use_fractions'
                            ],
                            [
                                'title' => 'Mostrar Gráfico',
                                'value' => 'show_graph',
                                'description' => 'Visualización gráfica del análisis paramétrico.',
                                'name' => 'show_graph'
                            ]
                        ]"
                        :checkedValues="['show_ranges', 'use_fractions', 'show_graph']"
                    />
                </div>

                {{-- Status Card --}}
                <div class="p-4 rounded-lg bg-indigo-50 border border-indigo-100 dark:bg-indigo-900/20 dark:border-indigo-800 transition-colors">
                    <h4 class="font-bold text-indigo-800 dark:text-indigo-400 mb-1 flex items-center gap-2">
                        <span class="text-xl">🎯</span> Estado Actual
                    </h4>
                    <div class="flex justify-between items-end mt-2 pt-2 border-t border-indigo-200 dark:border-indigo-800">
                        <span class="text-sm text-indigo-700 dark:text-indigo-300">Z Óptimo:</span>
                        <span class="font-mono font-bold text-lg text-indigo-900 dark:text-white"
                              x-text="currentTableau ? currentTableau.zValue : '0'"></span>
                    </div>
                    <div class="flex justify-between items-end mt-1 pt-1 border-t border-indigo-200 dark:border-indigo-800">
                        <span class="text-sm text-indigo-700 dark:text-indigo-300">Variables:</span>
                        <span class="font-mono text-sm text-indigo-900 dark:text-white"
                              x-text="variables.length"></span>
                    </div>
                </div>
            </div>
        </x-slot>

        {{-- 🔵 Center: Sensitivity Results --}}
        <div class="mb-8 h-full">
            <template x-if="!result">
                <div class="flex flex-col items-center justify-center h-full min-h-[400px] bg-slate-50 dark:bg-slate-800/50 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-700 text-slate-400">
                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <p class="text-lg font-medium">Análisis de Sensibilidad</p>
                    <p class="text-sm">Ingresa coeficientes y restricciones para comenzar.</p>
                </div>
            </template>

            {{-- Results Display --}}
            <template x-if="result">
                @include('tools.optimization.sensitivity.partials.results')
            </template>
        </div>

        {{-- 🔴 Right: Inputs --}}
        <x-slot:rightSidebar>
            @include('tools.optimization.sensitivity.partials.inputs')
        </x-slot>
    </x-three-column-tool>
</div>
