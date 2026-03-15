<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Role;

class RolesComposer {

    public function compose(View $view) {
        $roles = Role::orderBy('name', 'asc')->get();

        $view->with('roles', $roles);
    }
}
