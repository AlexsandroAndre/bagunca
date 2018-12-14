<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLojaPedidoProdutoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loja_pedido_produto', function (Blueprint $table) {
            $table->integer('pedido'); //chave estrangeira da tabela LOJA_PEDIDO
            $table->char('codigo_filial_origem'); //chave estrangeira da tabela LOJA_PEDIDO
            $table->integer('item'); //sequencial de itens do pedido, incrementar e não repetir
            $table->integer('id_vendedor'); //deixar fixo 1
            $table->string('codigo_barra', 25); //código de barra do item, relacionado com tabela PRODUTOS_BARRA
            $table->char('produto'); //codigo do produto, relacionado com tabela PRODUTOS_BARRA
            $table->char('cor_produto'); //codigo da cor, relacionado com a tabela PRODUTOS_BARRA
            $table->integer('tamanho'); //Ordem do tamanho da grade, relacionado com a coluna TAMANHO da tabela PRODUTOS_BARRA
            $table->integer('qtde'); //quantidade de peças do item
            $table->decimal('preco_liquido', 14,2); //preço praticado do item na venda
            $table->decimal('desconto_item', 14,2); //valor do desconto no item
            $table->integer('indica_item_ecommerce'); //preencher com 1
            $table->integer('qtde_venda'); //Deixar 0
            $table->integer('qtde_terceiro'); //Deixar 0
            $table->integer('qtde_devolvido'); //Deixar 0
            $table->integer('indica_entrega_futura'); //Deixar 0
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loja_pedido_produto');
    }
}
