<?php
namespace App\Http\Controllers\V1\Catalogos;

use App\Models\Catalogos\SubCategoriasCie10;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HttpResponse;

use App\Http\Requests;
use \Validator,\Hash, \Response, \DB;
use Illuminate\Support\Facades\Input;

use App\Models\Catalogos\GruposCie10;
use App\Models\Catalogos\CategoriasCie10;

class GrupoCie10Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $parametros = Input::only('q','page','per_page');
        if ($parametros['q']) {
            $data =  GruposCie10::where(function($query) use ($parametros) {
                $query->where('id','LIKE',"%".$parametros['q']."%")
                    ->orWhere('nombre','LIKE',"%".$parametros['q']."%");
            });
        } else {
            $data =  GruposCie10::getModel()->with('categoriasCie10');
        }


        if(isset($parametros['page'])){

            $resultadosPorPagina = isset($parametros["per_page"])? $parametros["per_page"] : 20;
            $data = $data->paginate($resultadosPorPagina);
        } else {
            $data = $data->get();
        }

        return Response::json([ 'data' => $data],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $datos = Input::json()->all();

        $success = false;
        $errors_main = array();
        DB::beginTransaction();

        try {
            if(array_key_exists('grupos_cie10', $datos)) { // Insertar más de un registro
                foreach ($datos['grupos_cie10'] as $key => $value) {
                    $validacion = $this->ValidarParametros($key, NULL, $value);
                    if($validacion != ""){
                        array_push($errors_main, $validacion);
                    }

                    $data = new GruposCie10;

                    $data->nombre = $value['nombre'];

                    if ($data->save())
                        $datos = (object) $datos;
                    $this->AgregarDatos($datos, $data);
                    $success = true;
                }

                if(count($errors_main)>0) {
                    return Response::json(['error' => array_collapse($errors_main)], HttpResponse::HTTP_CONFLICT);
                }
            } else {

                $validacion = $this->ValidarParametros("", NULL, $datos);
                if($validacion != ""){
                    return Response::json(['error' => $validacion], HttpResponse::HTTP_CONFLICT);
                }

                $data = new GruposCie10;

                $data->nombre = $datos['nombre'];

                if ($data->save())
                    $datos = (object) $datos;
                $this->AgregarDatos($datos, $data);
                $success = true;
            }
        } catch (\Exception $e){
            return Response::json($e->getMessage(), 500);
        }

        if ($success){
            DB::commit();
            return Response::json(array("status" => 201,"messages" => "Creado","data" => $data), 201);
        } else{
            DB::rollback();
            return Response::json(array("status" => 409,"messages" => "Conflicto"), 409);
        }
    }

    /**
     * Elimine el registro especificado del la base de datos (softdelete).
     *
     * @param  int  $id que corresponde al identificador del dato a eliminar
     *
     * @return Response
     * <code style="color:green"> Respuesta Ok json(array("status": 200, "messages": "Operación realizada con exito", "data": array(resultado)),status) </code>
     * <code> Respuesta Error json(array("status": 500, "messages": "Error interno del servidor"),status) </code>
     */
    public function destroy($id)
    {
        $success = false;
        DB::beginTransaction();
        try {
            $data = Checklists::find($id);
            if($data)
                $data->delete();
            $success = true;
        }
        catch (\Exception $e){
            return Response::json($e->getMessage(), 500);
        }
        if ($success){
            DB::commit();
            return Response::json(array("status" => 200, "messages" => "Operación realizada con exito","data" => $data), 200);
        }
        else {
            DB::rollback();
            return Response::json(array("status" => 404, "messages" => "No se encontro el registro"), 404);
        }
    }

    /**
     * Validad los parametros recibidos, Esto no tiene ruta de acceso es un metodo privado del controlador.
     *
     * @param  Request  $request que corresponde a los parametros enviados por el cliente
     *
     * @return Response
     * <code> Respuesta Error json con los errores encontrados </code>
     */
    private function ValidarParametros($key, $id, $request){

        $messages = [
            'required' => 'required',
            'unique' => 'unique'
        ];

        /*
        if($request['nivel_cone']) {
            $nivel_cone = $request['nivel_cone'];
        } else {
            $nivel_cone = NULL;
        }
        */
        $rules = [
            'nombre' => 'required|min:3|max:250|unique:grupos_cie10',
        ];

        $v = Validator::make($request, $rules, $messages);

        if ($v->fails()){
            $mensages_validacion = array();
            foreach ($v->errors()->messages() as $indice => $item) { // todos los mensajes de todos los campos
                $msg_validacion = array();
                foreach ($item as $msg) {
                    array_push($msg_validacion, $msg);
                }
                array_push($mensages_validacion, array($indice.''.$key => $msg_validacion));
            }
            return $mensages_validacion;
        }else{
            return ;
        }
    }

    private function AgregarDatos($datos, $data){
        //verificar si existe resguardos, en caso de que exista proceder a guardarlo
        if(property_exists($datos, "categorias_cie10")){
            //limpiar el arreglo de posibles nullos
            $detalle = array_filter($datos->categorias_cie10, function($v){return $v !== null;});
            //borrar los datos previos de articulo para no duplicar información
            CategoriasCie10::where("grupos_cie10_id", $data->id)->delete();
            //recorrer cada elemento del arreglo
            foreach ($detalle as $key => $value) {
                //validar que el valor no sea null
                if($value != null){
                    //comprobar si el value es un array, si es convertirlo a object mas facil para manejar.
                    if(is_array($value))
                        $value = (object) $value;

                    //comprobar que el dato que se envio no exista o este borrado, si existe y esta borrado poner en activo nuevamente
                    DB::select("update categorias_cie10 set deleted_at = null where grupos_cie10_id = '$data->id' and nombre = '$value->nombre' ");
                    //si existe el elemento actualizar
                    $categoria = CategoriasCie10::where("grupos_cie10_id", $data->id)->where("nombre", $value->nombre)->first();
                    //si no existe crear
                    if(!$categoria)
                        $categoria = new CategoriasCie10;

                    $categoria->grupos_cie10_id 	= $data->id;
                    $categoria->nombre              = $value->nombre;

                    if ($categoria->save()){
                        if(property_exists($value, "subcategorias_cie10")){

                            //limpiar el arreglo de posibles nullos
                            $detalle = array_filter($value->subcategorias_cie10, function($v){return $v !== null;});
                            //borrar los datos previos de articulo para no duplicar información

                            SubCategoriasCie10::where("categorias_cie10_id", $categoria->id)->delete();

                            //recorrer cada elemento del arreglo
                            foreach ($detalle as $key => $val) {
                                //validar que el valor no sea null
                                if($val != null){
                                    //comprobar si el value es un array, si es convertirlo a object mas facil para manejar.
                                    if(is_array($val))
                                        $val = (object) $val;

                                    //comprobar que el dato que se envio no exista o este borrado, si existe y esta borrado poner en activo nuevamente
                                    DB::select("update subcategorias_cie10 set deleted_at = null where categorias_cie10_id = '$categoria->id' and nombre = '$val->nombre' ");
                                    //si existe el elemento actualizar
                                    $subCategoria = SubCategoriasCie10::where("categorias_cie10_id", $categoria->id)->where("nombre", $val->nombre)->first();
                                    //si no existe crear
                                    if(!$subCategoria)
                                        $subCategoria = new SubCategoriasCie10;

                                    $subCategoria->categorias_cie10_id 	= $categoria->id;
                                    $subCategoria->nombre               = $val->nombre;

                                    $subCategoria->save();
                                }
                            }
                        }
                    }

                }
            }


        }
    }
}