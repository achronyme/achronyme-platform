@php
$breadcrumbs = [
    ['name' => 'Herramientas', 'url' => route('home')],
    ['name' => 'Optimización', 'url' => '#'],
    ['name' => 'Análisis de Sensibilidad', 'url' => '#']
];
@endphp

<x-tool-layout title="Análisis de Sensibilidad" :breadcrumbs="$breadcrumbs" icon="tune">
    <x-slot:actions>
        <x-secondary-button>Volver</x-secondary-button>
        <x-primary-button class="ml-3">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Exportar Reporte
        </x-primary-button>
    </x-slot>

    {{-- Specific content --}}
    @include('tools.optimization.sensitivity.sensitivity-content')

    <x-slot:scripts>
        @vite('resources/js/optimization/sensitivity/app.ts')
    </x-slot>
</x-tool-layout>
