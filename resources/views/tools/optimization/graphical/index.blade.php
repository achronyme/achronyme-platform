@php
$breadcrumbs = [
    ['name' => 'Herramientas', 'url' => route('home')],
    ['name' => 'Optimización', 'url' => '#'],
    ['name' => 'Método Gráfico', 'url' => '#']
];
@endphp

<x-tool-layout title="Método Gráfico" :breadcrumbs="$breadcrumbs" icon="area-chart">
    <x-slot:actions>
        <x-secondary-button>Volver</x-secondary-button>
    </x-slot>

    @include('tools.optimization.graphical.graphical-content')

    <x-slot:scripts>
        @vite('resources/js/optimization/graphical/app.ts')
    </x-slot>
</x-tool-layout>