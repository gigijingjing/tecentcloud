<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Goods\Goods;

class ChangeIncomeToGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Goods::query()
            ->where('income', 'NaN')
            ->update([
                'income' => 0,
            ]);
        Goods::query()
            ->where('conversion', 'NaN')
            ->update([
                'conversion' => 0,
            ]);
        Schema::table('goods', function (Blueprint $table) {
            $table->float('conversion')->change();
            $table->float('income')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goods', function (Blueprint $table) {
            //
        });
    }
}
