<div class="space-y-8">
    <div>
        {{-- Form Header --}}
        <div class="flex items-center gap-2 mb-4">
            <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/50 rounded-md text-indigo-600 dark:text-indigo-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="font-medium text-slate-900 dark:text-white">Definición del Problema</h3>
        </div>

        <div class="space-y-6">

            {{-- A. Dynamic Objective Function --}}
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
                            <x-app-ui.input-addon
                                label=""
                                x-addon="'x' + v.id"
                                x-model="v.zCoef"
                                placeholder="0"
                            />
                        </div>
                    </template>
                </div>
            </div>

            {{-- B. Dynamic Constraints --}}
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

                            {{-- Constraint Header --}}
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-mono text-slate-400" x-text="'R' + (index + 1)"></span>
                                <button @click="removeConstraint(c.id)" class="text-slate-300 hover:text-red-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            {{-- Coefficients Grid --}}
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-3">
                                <template x-for="v in variables" :key="v.id">
                                    <div>
                                        <x-app-ui.input-addon
                                            label=""
                                            x-addon="'x' + v.id"
                                            x-model="c.coefficients[v.name]"
                                            placeholder="0"
                                        />
                                    </div>
                                </template>
                            </div>

                            {{-- Operator and RHS --}}
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

    {{-- Action Button --}}
    <div class="pt-4">
        <x-app-ui.button @click="analyze()" loading-text="Analizando..." class="w-full flex items-center justify-center gap-2 py-3 text-lg shadow-lg shadow-indigo-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <span>Ejecutar Análisis</span>
        </x-app-ui.button>
    </div>

    {{-- Parametric Analysis Controls (shown when result exists) --}}
    <template x-if="result && result.coefficientRanges.length > 0">
        <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="p-1.5 bg-purple-100 dark:bg-purple-900/50 rounded-md text-purple-600 dark:text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                </div>
                <h3 class="font-medium text-slate-900 dark:text-white">Análisis Paramétrico</h3>
            </div>

            {{-- Variable Selector --}}
            <div class="mb-4">
                <label class="block text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider mb-2">
                    Variable a Analizar
                </label>
                <select @change="selectVariableForAnalysis($event.target.value); analyze();"
                        class="block w-full rounded-lg border-0 py-2 px-3 text-slate-900 dark:text-white bg-white dark:bg-slate-800 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">-- Selecciona una variable --</option>
                    <template x-for="v in variables" :key="v.id">
                        <option :value="v.name" x-text="v.name + ' (C = ' + v.zCoef + ')'"></option>
                    </template>
                </select>
            </div>

            {{-- Parameter Slider --}}
            <template x-if="selectedVariable">
                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                    <label class="block text-xs font-bold uppercase text-purple-700 dark:text-purple-400 tracking-wider mb-2">
                        Valor del Coeficiente
                    </label>

                    <div class="mb-3">
                        <input type="range"
                               x-model.number="parameterValue"
                               :min="minParameterValue"
                               :max="maxParameterValue"
                               step="0.1"
                               @input="updateParametricValue($event.target.value)"
                               class="w-full h-2 bg-purple-200 dark:bg-purple-800 rounded-lg appearance-none cursor-pointer accent-purple-600">
                    </div>

                    <div class="flex justify-between text-xs text-purple-600 dark:text-purple-400 mb-2">
                        <span x-text="'Min: ' + minParameterValue.toFixed(1)"></span>
                        <span class="font-bold text-base" x-text="parameterValue.toFixed(2)"></span>
                        <span x-text="'Max: ' + maxParameterValue.toFixed(1)"></span>
                    </div>

                    <div class="mt-3 pt-3 border-t border-purple-200 dark:border-purple-800">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-purple-700 dark:text-purple-300">Z Proyectado:</span>
                            <span class="font-mono font-bold text-lg text-purple-900 dark:text-white"
                                  x-text="currentZValue"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
