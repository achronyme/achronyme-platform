@php
$breadcrumbs = [
    ['name' => 'Herramientas', 'url' => route('home')],
    ['name' => 'Optimización', 'url' => '#'],
    ['name' => 'Método Dual Simplex', 'url' => '#']
];
@endphp

<x-tool-layout title="Método Dual Simplex" :breadcrumbs="$breadcrumbs" icon="table-view">
    <x-slot:actions>
        <x-secondary-button>Volver</x-secondary-button>
        <x-primary-button class="ml-3">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Exportar Reporte
        </x-primary-button>
    </x-slot>

    {{-- Contenido específico --}}
    @include('tools.optimization.dual-simplex.dual-simplex-content')

    <x-slot:scripts>
        @vite('resources/js/optimization/dual-simplex/app.ts')
    </x-slot>
</x-tool-layout>
