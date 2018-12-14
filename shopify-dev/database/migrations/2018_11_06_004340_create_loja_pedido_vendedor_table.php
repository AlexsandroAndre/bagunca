<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLojaPedidoVendedorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loja_pedido_vendedor', function (Blueprint $table) {
            $table->integer('pedido'); //chave estrangeira da tabela LOJA_PEDIDO
            $table->char('codigo_filial_origem'); //chave estrangeira da tabela LOJA_PEDIDO
            $table->integer('id_vendedor'); //deixar fixo 1
            $table->integer('tipo_vendedor'); //deixar fixo 1
            $table->char('vendedor'); //código do vendedor
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loja_pedido_vendedor');
    }
}
