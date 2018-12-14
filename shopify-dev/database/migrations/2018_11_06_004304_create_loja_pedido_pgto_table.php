<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLojaPedidoPgtoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loja_pedido_pgto', function (Blueprint $table) {
            $table->integer('pedido'); //chave estrangeira da tabela LOJA_PEDIDO
            $table->char('codigo_filial_origem'); //chave estrangeira da tabela LOJA_PEDIDO
            $table->char('parcela'); //numero da parcela, incrementar a cada nova parcela ou meio de pgto
            $table->integer('tipo_pgto'); //Tipo de pgto da parcela, validar com os dados da tabela TIPOS_PGTO, Ex: A = Cartão de credito, E = Cartão de debito, J = Duplicada/boleto, etc
            $table->decimal('valor', 14,2); //Valor da parcela
            $table->dateTime('vencimento'); //Data de vencimento da parcela, preencher data atual se for a vista
            $table->string('numero_titulo', 15); //Obrigatório Preencher com o DOC da autorização do cartão, deixar nulo se for tipo de pagamento Boleto
            $table->char('moeda'); //deixar fixo R$
            $table->decimal('valor_moeda', 14,2); //deixar fixo 0
            $table->char('codigo_administradora'); //validar na tabela ADMINISTRADORAS_CARTAO, campo obrigatório para meio de pgto com cartões
            $table->string('numero_aprovacao_cartao', 10); //número da aprovação do cartão, obrigatório para pagamento com cartões, ou deixar nulo caso boleto
            $table->integer('parcelas_cartao'); //informar o numero de parcelas caso seja cartão parcelado, informar 1 caso não tenha parcelamento.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loja_pedido_pgto');
    }
}
