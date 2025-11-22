<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index()
    {
        $tools = [
            [
                'title' => 'Transformada de Fourier',
                'description' => 'Calcular el espectro de magnitud y fase de una señal.',
                'url' => route("fourier-transform"),
                'icon' => 'icon-fx',
                'target' => '_blank'
            ],
            [
                'title' => 'Serie de Fourier',
                'description' => 'Analizar funciones periódicas descomponiéndolas en senos y cosenos.',
                'url' => route("fourier-series"),
                'icon' => 'icon-sf',
                'target' => '_blank'
            ],
            [
                'title' => 'Convolución',
                'description' => 'Permite visualizar la convolución de dos señales en tiempo real.',
                'url' => route("convolution"),
                'icon' => 'icon-conv',
                'target' => '_blank'
            ],
            [
                'title' => 'Método Gráfico',
                'description' => 'Resolver problemas de optimización lineal (2 variables) visualizando la región factible.',
                'url' => route("graphical-method"),
                'icon' => 'icon-area-chart',
                'target' => '_blank'
            ],
            [
                'title' => 'Método Simplex',
                'description' => 'Resuelve problemas de programación lineal paso a paso mediante iteraciones tabulares.',
                'url' => route("simplex-method"),
                'icon' => 'icon-table-view',
                'target' => '_blank'
            ],
            [
                'title' => 'Dual Simplex',
                'description' => 'Resuelve problemas de programación lineal paso a paso mediante iteraciones tabulares.',
                'url' => route("dual-simplex-method"),
                'icon' => 'icon-table-view',
                'target' => '_blank'
            ],
            [
                'title' => 'Visualizador de Agentes',
                'description' => 'Explora algoritmos de búsqueda (BFS/DFS) en entornos de agentes.',
                'url' => route("agent-visualizer"),
                'icon' => 'icon-agent',
                'target' => '_blank'
            ],
            [
                'title' => 'GitHub',
                'description' => 'Explora el código fuente, la documentación y contribuye al proyecto.',
                'url' => 'https://github.com/eddndev/achronyme',
                'icon' => 'icon-github',
                'target' => '_blank'
            ],
        ];
        return view('welcome', ['tools' => $tools]);
    }
}