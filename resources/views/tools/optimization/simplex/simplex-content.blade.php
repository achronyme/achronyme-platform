<div x-data="{ objectiveType: 'max' }"> {{-- Estado temporal simple --}}
    <x-three-column-tool>
        {{-- 🟢 Izquierda: Configuración del Algoritmo --}}
        <x-slot:leftSidebar>
            <div class="space-y-6">
                <x-app-ui.radio-list
                    legend="Objetivo"
                    name="objective_type"
                    :options="[
                        ['value' => 'max', 'title' => 'Maximizar', 'description' => 'Algoritmo Simplex estándar'],
                        ['value' => 'min', 'title' => 'Minimizar', 'description' => 'Dual-Simplex o Big M']
                    ]"
                    x-model="objectiveType"
                />

                <div class="p-4 rounded-lg border bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 mb-2 text-sm">Configuración Avanzada</h4>
                    <div class="space-y-3">
                        <x-app-ui.checkbox id="show_steps" value="1" label="Mostrar paso a paso" checked />
                        <x-app-ui.checkbox id="use_fractions" value="1" label="Usar Fracciones" checked />
                    </div>
                </div>

                {{-- Card de Estado (Dummy) --}}
                <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800">
                    <h4 class="font-bold text-emerald-800 dark:text-emerald-400 mb-1 flex items-center gap-2">
                        <span class="text-xl">✨</span> Solución Óptima
                    </h4>
                    <p class="text-sm text-emerald-700 dark:text-emerald-300">Iteración #3 alcanzada.</p>
                    <div class="mt-2 pt-2 border-t border-emerald-200 dark:border-emerald-800 font-mono text-sm">
                        Z = 1,250.00
                    </div>
                </div>
            </div>
        </x-slot>

        {{-- 🔵 Centro: Tabla Simplex (Tableau) --}}
        <div class="mb-8 h-full">
            <div class="bg-white dark:bg-slate-800 p-1 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-700 dark:text-slate-300">Tabla Inicial (Iteración 0)</h3>
                    <span class="px-2 py-1 text-xs font-bold rounded bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">Base Factible</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-100 dark:bg-slate-700 dark:text-slate-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Base</th>
                                <th scope="col" class="px-6 py-3">Z</th>
                                <th scope="col" class="px-6 py-3">X1</th>
                                <th scope="col" class="px-6 py-3">X2</th>
                                <th scope="col" class="px-6 py-3">S1</th>
                                <th scope="col" class="px-6 py-3">S2</th>
                                <th scope="col" class="px-6 py-3 bg-slate-200 dark:bg-slate-600">RHS</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Filas Dummy --}}
                            <tr class="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700">
                                <th scope="row" class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap dark:text-white">S1</th>
                                <td class="px-6 py-4">0</td>
                                <td class="px-6 py-4">2</td>
                                <td class="px-6 py-4">1</td>
                                <td class="px-6 py-4">1</td>
                                <td class="px-6 py-4">0</td>
                                <td class="px-6 py-4 font-bold bg-slate-50 dark:bg-slate-800/50">100</td>
                            </tr>
                            <tr class="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700">
                                <th scope="row" class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap dark:text-white">S2</th>
                                <td class="px-6 py-4">0</td>
                                <td class="px-6 py-4">1</td>
                                <td class="px-6 py-4">3</td>
                                <td class="px-6 py-4">0</td>
                                <td class="px-6 py-4">1</td>
                                <td class="px-6 py-4 font-bold bg-slate-50 dark:bg-slate-800/50">80</td>
                            </tr>
                            {{-- Fila Z --}}
                            <tr class="bg-indigo-50 dark:bg-indigo-900/20 border-t-2 border-indigo-100 dark:border-indigo-800">
                                <th scope="row" class="px-6 py-4 font-bold text-indigo-700 dark:text-indigo-400">Z</th>
                                <td class="px-6 py-4 font-bold text-indigo-700 dark:text-indigo-400">1</td>
                                <td class="px-6 py-4">-30</td>
                                <td class="px-6 py-4">-20</td>
                                <td class="px-6 py-4">0</td>
                                <td class="px-6 py-4">0</td>
                                <td class="px-6 py-4 font-bold text-indigo-700 dark:text-indigo-400">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 🔴 Derecha: Inputs del Modelo --}}
        <x-slot:rightSidebar>
            <div class="space-y-8">
                {{-- Header --}}
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/50 rounded-md text-indigo-600 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="font-medium text-slate-900 dark:text-white">Definición del Problema</h3>
                    </div>
                    
                    {{-- Placeholder de Inputs (Copiaremos la lógica dinámica después) --}}
                    <div class="p-4 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg text-center text-slate-500">
                        <p class="text-sm mb-2">Aquí irán los inputs dinámicos de variables y restricciones.</p>
                        <p class="text-xs opacity-75">(Reutilizaremos el componente del Método Gráfico)</p>
                    </div>
                </div>

                <div class="pt-4">
                    <x-app-ui.button class="w-full flex items-center justify-center gap-2 py-3 text-lg shadow-lg shadow-indigo-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span>Resolver con Simplex</span>
                    </x-app-ui.button>
                </div>
            </div>
        </x-slot>
    </x-three-column-tool>
</div>