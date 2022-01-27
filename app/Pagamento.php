<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $table = 'pagamentos';

    protected $primaryKey = 'i_pagamento';

    public $timestamps = false;

    protected $fillable = [
        'i_pagamento',
        'i_empresa',
        'nome',
        'cpf_cnpj',
        'endereco',
        'cidade',
        'uf',
        'valor',
        'parcelas',
        'num_pedido',
        'tid',
        'codret',
        'mensagemret',
        'status',
        'usuario',
        'dt_sistema',
        'descricao',
        'msg_errors',
        'adquirente',
        'tipo'
    ];
}