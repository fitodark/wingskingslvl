<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Services\BandejaService;

class SalesComposer {

    public function compose(View $view) {
        $bandeja = app(BandejaService::class);
        $view->with('ventas', $bandeja->ventas())
            ->with('i', (request()->input('page', 1) - 1) * BandejaService::POR_PAGINA)
            // Punto de partida del refresco automático (public/js/bandeja.js).
            ->with('bandejaFirma', $bandeja->firma())
            ->with('bandejaMaxFolio', $bandeja->maxFolio());
    }
}
