<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBannerUrlToGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->string('banner_url')->nullable()->comment('产品横幅');
            $table->string('tags')->nullable()->comment('产品标签');
            $table->string('popularity')->nullable()->comment('产品人气');
            $table->string('loan_speed')->nullable()->comment('放款速度');
            $table->string('loan_range')->nullable()->comment('放款率');
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
            $table->dropColumn(['banner_url', 'tags', 'popularity', 'loan_speed', 'loan_range']);
        });
    }
}
