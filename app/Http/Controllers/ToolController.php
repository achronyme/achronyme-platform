<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ToolController extends Controller
{
    /**
     * Display the fourier series tool page.
     */
    public function fourierSeries(): View
    {
        return view('tools.fourier.fs.index');
    }

    /**
     * Display the fourier transform tool page.
     */
    public function fourierTransform(): View
    {
        return view('tools.fourier.ft.index');
    }

    /**
     * Display the convolution tool page.
     */
    public function convolution(): View
    {
        return view('tools.convolution.index');
    }

    /**
     * Display the agent visualizer tool page.
     */
    public function agentVisualizer(): View
    {
        return view('tools.agents.index');
    }

    /**
     * Display the graphical method tool page.
     */
    public function graphicalMethod(): View
    {
        // Retorna la vista principal del módulo gráfico
        return view('tools.optimization.graphical.index');
    }

    /**
     * Display the simplex method tool page.
     */
    public function simplexMethod(): View
    {
        // Retorna la vista principal del módulo simplex
        return view('tools.optimization.simplex.index');
    }
}
