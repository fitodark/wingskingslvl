<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Traits\CorteCajaDataLibrary;

class CorteCajaComposer {

    use CorteCajaDataLibrary;

    public function compose(View $view) {
        $corteCajaCurrent = $this->getCurrentCorteCaja(null);

        $view->with('corteCajaCurrent', $corteCajaCurrent[0]);
    }
}
