<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientesVarejoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes_varejo', function (Blueprint $table) {
            $table->string('codigo_cliente'); //código do cliente, campo chave, recomendável usar o CPF ou CNPJ como código, preencher com zeros a esquerda
            $table->string('cliente_varejo'); //nome resumido do cliente
            $table->string('filial'); //deixar fixo SITE
            $table->integer('pj_pf'); //pessoa juridica = 1, pessoa fisica = 0
            $table->string('rg_ie'); //informar o RG ou IE, sem traços, pontos, etc
            $table->string('cpf_cgc'); //informar o CPF ou CNPJ, sem traços, pontos, etc
            $table->string('cidade'); //informar a cidade
            $table->char('uf'); //informar a sigla da UF, se for exterior informar EX
            $table->string('endereco'); //endereço
            $table->string('numero'); //nuumero do endereço
            $table->string('complemento'); //informar o complemento do endereco
            $table->string('bairro'); //informar o bairro do endereço
            $table->string('cep'); //informar o CEP
            $table->string('telefone'); //informar o fone
            $table->char('ddd'); //informar o DDD
            $table->dateTime('aniversario'); //informar aniversario do cliente
            $table->dateTime('cadastramento'); //informar data de cadastro
            $table->char('sexo'); //Informar sexo (M = Masculino, F = feminino)
            $table->longText('obs'); //Campo de obs, livre
            $table->string('email'); //informar o e-mail
            $table->integer('status'); //Fixo 1
            $table->string('pais'); //informar o pais, relacionado com tabela PAISES
            $table->string('profissao'); 
            $table->string('celular'); //informar celular
            $table->string('ddd_celular'); //ddd do celular
            $table->integer('enviado_spc'); //0 = não, 1 = sim
            $table->integer('inativo_para_crm'); //0 = não, 1 = sim
            $table->string('nome_mae'); //nome da mae
            $table->string('nome_familia'); //sobrenome família
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clientes_varejo');
    }
}
