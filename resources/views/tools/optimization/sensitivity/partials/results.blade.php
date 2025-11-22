<div class="space-y-6" x-show="settings.includes('show_ranges')" x-transition>

    {{-- Coefficient Sensitivity Ranges Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <h3 class="font-bold text-slate-700 dark:text-slate-300 text-lg flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Rangos de Sensibilidad para Coeficientes
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Cambios permitidos en cada coeficiente sin afectar la base óptima
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                <thead class="text-xs text-slate-700 uppercase bg-slate-100 dark:bg-slate-700 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 border-r dark:border-slate-600">Variable</th>
                        <th class="px-4 py-3 text-center border-r dark:border-slate-600">Valor Actual</th>
                        <th class="px-4 py-3 text-center border-r dark:border-slate-600">Estado</th>
                        <th class="px-4 py-3 text-center border-r dark:border-slate-600">Aumento Permitido</th>
                        <th class="px-4 py-3 text-center border-r dark:border-slate-600">Disminución Permitida</th>
                        <th class="px-4 py-3 text-center bg-slate-200 dark:bg-slate-600 font-bold" colspan="2">Rango [Min, Max]</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <template x-for="(range, index) in result.coefficientRanges" :key="index">
                        <tr class="bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <th class="px-4 py-3 font-bold text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-800/50 border-r dark:border-slate-600"
                                x-text="range.variable">
                            </th>

                            <td class="px-4 py-3 text-center font-mono border-r dark:border-slate-600"
                                x-text="range.currentValue">
                            </td>

                            <td class="px-4 py-3 text-center border-r dark:border-slate-600">
                                <span class="px-2 py-1 text-xs font-bold rounded-full"
                                      :class="range.isBasic ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-400'"
                                      x-text="range.isBasic ? 'Básica' : 'No Básica'">
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center font-mono border-r dark:border-slate-600 text-green-600 dark:text-green-400"
                                x-text="range.allowableIncrease">
                            </td>

                            <td class="px-4 py-3 text-center font-mono border-r dark:border-slate-600 text-red-600 dark:text-red-400"
                                x-text="range.allowableDecrease">
                            </td>

                            <td class="px-4 py-3 text-center font-mono bg-slate-50 dark:bg-slate-800/50"
                                x-text="range.minValue">
                            </td>

                            <td class="px-4 py-3 text-center font-mono bg-slate-50 dark:bg-slate-800/50"
                                x-text="range.maxValue">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Visual Range Indicators --}}
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700">
            <h4 class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider mb-3">
                Indicadores Visuales de Rango
            </h4>
            <div class="space-y-3">
                <template x-for="(range, index) in result.coefficientRanges" :key="index">
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-bold text-slate-700 dark:text-slate-300" x-text="range.variable"></span>
                            <span class="font-mono text-slate-500 dark:text-slate-400">
                                <span x-text="range.minValue"></span> ←
                                <span class="text-indigo-600 dark:text-indigo-400 font-bold" x-text="range.currentValue"></span> →
                                <span x-text="range.maxValue"></span>
                            </span>
                        </div>
                        <div class="relative h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            {{-- Safe range (green) --}}
                            <div class="absolute inset-0 bg-gradient-to-r from-yellow-400 via-green-400 to-yellow-400 opacity-50"></div>
                            {{-- Current value indicator --}}
                            <div class="absolute top-0 bottom-0 w-1 bg-indigo-600 dark:bg-indigo-400"
                                 style="left: 50%; transform: translateX(-50%);">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Shadow Prices Table --}}
    <template x-if="analysisType === 'shadow_prices' && result.shadowPrices.length > 0">
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="font-bold text-slate-700 dark:text-slate-300 text-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Precios Sombra (Dual Values)
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Valor marginal de incrementar el lado derecho de cada restricción
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-100 dark:bg-slate-700 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3 border-r dark:border-slate-600">Restricción</th>
                            <th class="px-4 py-3 text-center border-r dark:border-slate-600">RHS Actual</th>
                            <th class="px-4 py-3 text-center border-r dark:border-slate-600 bg-purple-100 dark:bg-purple-900/30">Precio Sombra</th>
                            <th class="px-4 py-3 text-center border-r dark:border-slate-600">Aumento Permitido</th>
                            <th class="px-4 py-3 text-center">Disminución Permitida</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <template x-for="(sp, index) in result.shadowPrices" :key="index">
                            <tr class="bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                <th class="px-4 py-3 font-bold text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-800/50 border-r dark:border-slate-600"
                                    x-text="sp.constraint">
                                </th>

                                <td class="px-4 py-3 text-center font-mono border-r dark:border-slate-600"
                                    x-text="sp.currentRHS">
                                </td>

                                <td class="px-4 py-3 text-center font-mono font-bold border-r dark:border-slate-600 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400"
                                    x-text="sp.shadowPrice">
                                </td>

                                <td class="px-4 py-3 text-center font-mono border-r dark:border-slate-600 text-green-600 dark:text-green-400"
                                    x-text="sp.allowableIncrease">
                                </td>

                                <td class="px-4 py-3 text-center font-mono text-red-600 dark:text-red-400"
                                    x-text="sp.allowableDecrease">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-purple-50 dark:bg-purple-900/20 border-t border-purple-100 dark:border-purple-800">
                <p class="text-xs text-purple-700 dark:text-purple-300">
                    <span class="font-bold">Interpretación:</span> Un precio sombra positivo indica que incrementar el recurso aumentaría el valor óptimo de Z.
                    Un precio sombra de 0 indica que la restricción no es vinculante (hay holgura).
                </p>
            </div>
        </div>
    </template>

    {{-- Parametric Analysis Graph --}}
    <template x-if="settings.includes('show_graph') && selectedVariable && parametricPoints.length > 0">
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <h3 class="font-bold text-slate-700 dark:text-slate-300 text-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    Análisis Paramétrico: <span x-text="selectedVariable" class="text-indigo-600 dark:text-indigo-400"></span>
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Comportamiento de Z al variar el coeficiente seleccionado
                </p>
            </div>

            <div class="p-6">
                {{-- Simple ASCII-style graph representation --}}
                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-200 dark:border-slate-700 font-mono text-xs">
                    <div class="mb-2 text-slate-600 dark:text-slate-400">
                        Z vs Coeficiente <span x-text="selectedVariable"></span>
                    </div>
                    <div class="space-y-1">
                        <template x-for="(point, idx) in parametricPoints.slice().reverse()" :key="idx">
                            <div class="flex items-center gap-2">
                                <span class="w-20 text-right text-slate-500 dark:text-slate-400"
                                      x-text="point.coefficientValue + ':'"></span>
                                <div class="flex-1 h-4 bg-slate-200 dark:bg-slate-800 rounded overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 transition-all"
                                         :style="'width: ' + (parseFloat(point.zValue) / Math.max(...parametricPoints.map(p => parseFloat(p.zValue))) * 100) + '%'">
                                    </div>
                                </div>
                                <span class="w-16 text-slate-700 dark:text-slate-300 font-bold"
                                      x-text="point.zValue"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Data Points Table --}}
                <div class="mt-4 max-h-64 overflow-y-auto">
                    <table class="w-full text-xs">
                        <thead class="sticky top-0 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                            <tr>
                                <th class="px-3 py-2 text-left">Coeficiente</th>
                                <th class="px-3 py-2 text-center">Z Óptimo</th>
                                <th class="px-3 py-2 text-left">Base Óptima</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <template x-for="(point, idx) in parametricPoints" :key="idx">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    <td class="px-3 py-2 font-mono" x-text="point.coefficientValue"></td>
                                    <td class="px-3 py-2 font-mono text-center font-bold" x-text="point.zValue"></td>
                                    <td class="px-3 py-2 font-mono text-xs text-slate-500 dark:text-slate-400"
                                        x-text="point.basis.join(', ')"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </template>

    {{-- Optimal Tableau Reference --}}
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <h3 class="font-bold text-slate-700 dark:text-slate-300 text-lg flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Tableau Óptimo
            </h3>
        </div>

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
                                    x-text="val">
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

        <div class="p-3 bg-green-50 dark:bg-green-900/20 border-t border-green-100 dark:border-green-800">
            <div class="flex justify-between items-center">
                <span class="text-sm text-green-700 dark:text-green-300">Valor Óptimo de Z:</span>
                <span class="font-mono font-bold text-xl text-green-900 dark:text-white"
                      x-text="currentTableau.zValue"></span>
            </div>
        </div>
    </div>

</div>
