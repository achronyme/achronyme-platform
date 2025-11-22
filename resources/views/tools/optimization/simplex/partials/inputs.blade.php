<div class="space-y-8">
    <div>
        {{-- Header del Formulario --}}
        <div class="flex items-center gap-2 mb-4">
            <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/50 rounded-md text-indigo-600 dark:text-indigo-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="font-medium text-slate-900 dark:text-white">Definición del Problema</h3>
        </div>
        
        <div class="space-y-6">
            
            {{-- A. Función Objetivo Dinámica --}}
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
                            {{-- Usamos x-addon para el binding dinámico con Alpine --}}
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

            {{-- B. Restricciones Dinámicas --}}
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
                            
                            {{-- Cabecera de Restricción --}}
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-mono text-slate-400" x-text="'R' + (index + 1)"></span>
                                <button @click="removeConstraint(c.id)" class="text-slate-300 hover:text-red-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            {{-- Grid de Coeficientes --}}
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

                            {{-- Operador y Lado Derecho (SOL) --}}
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

    {{-- Botón de Acción --}}
    <div class="pt-4">
        <x-app-ui.button @click="solve()" loading-text="Optimizando..." class="w-full flex items-center justify-center gap-2 py-3 text-lg shadow-lg shadow-indigo-500/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <span>Ejecutar Simplex</span>
        </x-app-ui.button>
    </div>
</div>