<?php

namespace NCH\Codeforlife\Http\Controllers;

use Illuminate\Database\Schema\Blueprint;
use Schema;
use NCH\Codeforlife\Codeforlife;

class CodeforlifeUpgradeController extends Controller
{
    public function index()
    {
        $upgraded = $this->upgrade_v0_10_6();

        if ($upgraded) {
            return redirect()->route('codeforlife.dashboard')->with(['message' => 'Database Schema has been Updated.', 'alert-type' => 'success']);
        } else {
            return redirect()->route('codeforlife.dashboard');
        }
    }

    private function upgrade_v0_10_6()
    {
        if (!Schema::hasColumn('data_types', 'server_side')) {
            Schema::table('data_types', function (Blueprint $table) {
                $table->tinyInteger('server_side')->default(0)->after('generate_permissions');
            });

            return true;
        }

        return false;
    }
}
