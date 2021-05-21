<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('产品名称');
            $table->string('logo')->comment('产品logo');
            $table->string('url')->comment('产品地址');
            $table->string('status')->nullable()->comment('产品状态');
            $table->text('content')->nullable()->comment('产品内容');
            $table->integer('stock')->nullable()->comment('产品库存');
            $table->string('loan_ratio')->nullable()->comment('放款率');
            $table->string('day_profit_ratio')->nullable()->comment('日利率');
            $table->string('contact_name')->nullable()->comment('联系人名称');
            $table->string('contact_phone')->nullable()->comment('联系人电话');
            $table->integer('click_num')->nullable()->comment('产品点击量');
            $table->integer('register_num')->nullable()->comment('产品注册量');
            $table->string('mode')->nullable()->comment('合作模式');
            $table->string('cpa')->nullable()->comment('cpa单价');
            $table->string('cps')->nullable()->comment('cps分成比');
            $table->string('loan_price')->nullable()->comment('放款单价');
            $table->string('loan_money')->nullable()->comment('放款金额');
            $table->string('loan_num')->nullable()->comment('放款数量');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods');
    }
}
