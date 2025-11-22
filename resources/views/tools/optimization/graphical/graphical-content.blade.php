<div x-data="graphicalState()" x-init="init()">
    <x-three-column-tool>
        {{-- 🟢 Columna Izquierda: Configuración Global --}}
        <x-slot:leftSidebar>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-1 gap-6">
                
                {{-- Grupo 1: Selectores --}}
                <div class="space-y-6">
                    <x-app-ui.radio-list
                        legend="Objetivo"
                        name="objective_type"
                        :options="[
                            ['value' => 'max', 'title' => 'Maximizar', 'description' => 'Aumentar Z'],
                            ['value' => 'min', 'title' => 'Minimizar', 'description' => 'Reducir Z']
                        ]"
                        x-model="objectiveType"
                    />

                    <x-app-ui.radio-list
                        legend="Dimensiones"
                        name="num_variables"
                        :options="[
                            ['value' => 2, 'title' => '2 Variables (2D)', 'description' => 'Gráfica plana (X, Y)'],
                            ['value' => 3, 'title' => '3 Variables (3D)', 'description' => 'Volumen (X, Y, Z)']
                        ]"
                        x-model.number="numVariables"
                    />
                </div>

                {{-- Grupo 2: Zoom y Resultados --}}
                <div class="space-y-6">
                    <x-app-ui.slider
                        label="Zoom / Rango Ejes"
                        name="axis_range"
                        min="10" max="500" step="10"
                        x-model.number="axisRange"
                    />
                    
                    <div class="p-4 rounded-lg border bg-blue-50 border-blue-100 dark:bg-blue-900/20 dark:border-blue-800 transition-colors h-full">
                        <h4 class="font-bold text-blue-800 dark:text-blue-300 mb-3 flex items-center gap-2">
                            <span class="text-xl">📊</span> Resultado Óptimo
                        </h4>
                        
                        <template x-if="optimalPoint">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between items-center border-b border-blue-200 dark:border-blue-700 pb-2">
                                    <span class="text-slate-600 dark:text-slate-400">Valor Z:</span>
                                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 text-lg" x-text="optimalValue?.toFixed(4)"></span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2 pt-1">
                                    <div class="flex justify-between">
                                        <span class="text-slate-600 dark:text-slate-400">x₁:</span>
                                        <span class="font-mono text-slate-900 dark:text-white" x-text="optimalPoint.x.toFixed(4)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-600 dark:text-slate-400">x₂:</span>
                                        <span class="font-mono text-slate-900 dark:text-white" x-text="optimalPoint.y.toFixed(4)"></span>
                                    </div>
                                    <template x-if="numVariables === 3">
                                        <div class="flex justify-between col-span-2">
                                            <span class="text-slate-600 dark:text-slate-400">x₃:</span>
                                            <span class="font-mono text-slate-900 dark:text-white" x-text="optimalPoint.z?.toFixed(4) || '0.0000'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="!optimalPoint">
                            <div class="flex flex-col items-center text-center py-2 text-slate-500 dark:text-slate-400">
                                <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <p class="text-xs italic">Configura las variables y presiona "Calcular"</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </x-slot>

        {{-- 🔵 Centro: Visualización --}}
        <div class="mb-8 h-full">
            <div class="relative bg-slate-50 dark:bg-slate-800/50 p-1 rounded-lg h-full min-h-[400px] md:min-h-[500px] border border-slate-200 dark:border-slate-700 shadow-inner">
                <div id="graphicalChart" class="w-full h-full min-h-[400px] md:min-h-[500px] rounded bg-white dark:bg-[#1e293b]"></div>
            </div>
        </div>

        {{-- 🔴 Derecha: Inputs --}}
        <x-slot:rightSidebar>
            <div class="space-y-8">
                
                {{-- 1. Función Objetivo --}}
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/50 rounded-md text-indigo-600 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <h3 class="font-medium text-slate-900 dark:text-white">Función Objetivo (Z)</h3>
                    </div>

                    <div class="grid gap-3" :class="numVariables === 3 ? 'grid-cols-3' : 'grid-cols-2'">
                        <x-app-ui.input-addon label="" addon="x₁" x-model="objX1" name="obj_x1" placeholder="0" />
                        <x-app-ui.input-addon label="" addon="x₂" x-model="objX2" name="obj_x2" placeholder="0" />
                        <template x-if="numVariables === 3">
                            <x-app-ui.input-addon label="" addon="x₃" x-model="objX3" name="obj_x3" placeholder="0" />
                        </template>
                    </div>
                </div>

                <div class="border-t border-slate-200 dark:border-slate-700"></div>

                {{-- 2. Restricciones --}}
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-emerald-100 dark:bg-emerald-900/50 rounded-md text-emerald-600 dark:text-emerald-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Restricciones</h3>
                        </div>
                        
                        <x-app-ui.secondary-button type="button" @click="addConstraint()" class="py-2 px-3 text-sm flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Agregar
                        </x-app-ui.secondary-button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(c, index) in constraints" :key="c.id">
                            <div class="relative bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                                
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400" x-text="'Restricción ' + (index + 1)"></span>
                                    <x-app-ui.danger-circular-button 
                                        icon="trash" 
                                        @click="removeConstraint(c.id)" 
                                        title="Eliminar"
                                        class="!w-6 !h-6 !p-1"
                                    />
                                </div>
                                
                                <div class="grid gap-2 mb-3" :class="numVariables === 3 ? 'grid-cols-3' : 'grid-cols-2'">
                                    <x-app-ui.input-addon label="" addon="x₁" x-model="c.x1" name="c_x1" placeholder="0" />
                                    <x-app-ui.input-addon label="" addon="x₂" x-model="c.x2" name="c_x2" placeholder="0" />
                                    <template x-if="numVariables === 3">
                                        <x-app-ui.input-addon label="" addon="x₃" x-model="c.x3" name="c_x3" placeholder="0" />
                                    </template>
                                </div>

                                <div class="flex gap-2 items-center">
                                    <select x-model="c.operator" 
                                        class="block w-20 rounded-md border-0 py-1.5 text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 text-center font-bold cursor-pointer">
                                        <option value="<=">≤</option>
                                        <option value=">=">≥</option>
                                        <option value="=">=</option>
                                    </select>

                                    <div class="flex-1">
                                        <x-app-ui.input-text label="" name="rhs" x-model="c.rhs" placeholder="Valor (b)" />
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-4">
                    <x-app-ui.button @click="calculate()" loading-text="Optimizando..." class="w-full flex items-center justify-center gap-2 py-3 text-lg shadow-lg shadow-indigo-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Calcular Solución</span>
                    </x-app-ui.button>
                </div>

            </div>
        </x-slot>
    </x-three-column-tool>
</div>