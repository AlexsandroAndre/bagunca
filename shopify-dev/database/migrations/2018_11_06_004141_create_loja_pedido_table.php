<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLojaPedidoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loja_pedido', function (Blueprint $table) {
            $table->integer('pedido'); //número sequencial do pedido, deve ser obtido com base no seguindo comando: SELECT max(SEQUENCIA_PEDIDO) + 1 FROM lojas_varejo where FILIAL='SITE', ou utilizar numeração própria do site , campo chave não deve repetir. Caso usar o sequencial interno do linx, deve fazer o update no final da gravação para atualizar a sequencia na tabela LOJAS_VAREJO
            $table->char('codigo_filial_origem'); //Código de filial do pedido, relacionamento com tabela FILIAIS
            $table->char('codigo_filial_retirada'); //Deixar igual a filial origem, relacionamento com tabela FILIAIS
            $table->dateTime('data'); //preencher a data do pedido
            $table->integer('tipo_pedido'); //Deixar como 4 = pedido do site
            $table->char('vendedor'); //Código vendedor do pedido, deixar 0038
            $table->string('codigo_cliente'); //código do cliente do pedido, relacionamento com a tabela CLIENTES_VAREJO
            $table->integer('qtde_total'); //Total de itens do pedido
            $table->decimal('valor_total',14,2); //valor total do pedido
            $table->decimal('desconto',14,2); //total do desconto do pedido
            $table->char('operacao_venda'); //05 = venda varejo site, 06 = venda atacado site
            $table->char('cod_forma_pgto'); //Deixar fixo ##
            $table->char('cod_tab_preco'); //Deixar 01 para venda atacado, e 02 para venda varejo
            $table->longText('obs'); //Deixar fixo ##
            $table->decimal('frete',14,2); //Valor do frete total, Obs.: não deduzir do total da venda, apenas informativo
            $table->integer('cancelado'); //1 = cancelado, 0 = não
            $table->integer('entregue'); //deixar fixo 0
            $table->integer('digitacao_encerrada'); //deixar fixo 1
            $table->integer('lx_tipo_pre_venda'); //deixar fixo 1
            $table->char('pedido_integracao'); //Campo exclusivo customizado para ser utilizado como opcional para gravação de número de pedido para relacionamento com b2c
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loja_pedido');
    }
}
