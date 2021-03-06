<?php

namespace App;

namespace App\Models\Catalogos;
use App\Models\BaseModel;
use App\Models\Transacciones\RespuestasEstadosFuerza;
use Illuminate\Database\Eloquent\SoftDeletes;

class Items extends BaseModel
{
    public $incrementing = true;

    protected $table = "items";
    protected $fillable = ["id", "nombre", "cartera_servicios_id"];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function carteraServicio()
    {
        return $this->belongsTo(CarteraServicios::class);
    }

    public function respuestas_estados_fuerza()
    {
        return $this->hasMany(RespuestasEstadosFuerza::class);
    }

    public function tipoItem()
    {
        return $this->belongsTo(TiposItems::class);
    }
}
