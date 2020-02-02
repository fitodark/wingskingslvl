<?php

use App\Branchoffice;
use App\Branchofficeterminal;
use Illuminate\Database\Seeder;

class BranchOfficersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $branchoffice = new Branchoffice([
          'name' => 'Zaragoza',
          'alias' => 'ZRZ',
          'active' => '1'
        ]);
        $branchoffice->save();

        $branchTerminal = new Branchofficeterminal([
          'branchoffice_id' => $branchoffice->id,
          'name' => 'Terminal1',
          'active' => '1'
        ]);

        //$branchTerminal->branchoffice = $branchoffice;
        $branchTerminal->save();
    }
}
