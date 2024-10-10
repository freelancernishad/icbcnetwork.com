<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->decimal('daily_percentage');
            $table->decimal('total_percentage');
            $table->decimal('daily_income');
            $table->decimal('total_earnings');
            $table->integer('days');
            $table->decimal('price');
            $table->integer('cashback')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
