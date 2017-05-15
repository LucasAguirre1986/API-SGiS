<?php

namespace App;

namespace App\Models\Catalogos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NivelesCones extends Model
{
    use SoftDeletes;

    protected $generarID = false;
    protected $guardarIDServidor = false;
    protected $guardarIDUsuario = false;
    public $incrementing = true;

    protected $table = "niveles_cones";
    protected $fillable = ["id","nombre"];

    public function checklists(){
        return $this->belongsToMany(Checklists::class, 'checklist_nivel_cone', 'niveles_cones_id', 'checklists_id');
    }

}
