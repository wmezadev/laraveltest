<?php 

namespace App\AccessObject\Nutibara\MaquilaNacional;

use App\Models\Nutibara\OrdenResolucion\OrdenResolucion AS ModelMaquilaNacional;
use App\Models\Nutibara\Orden\Orden AS ModelOrden;
use App\Models\Nutibara\Tienda\Tienda AS ModelTienda;
use App\Models\Nutibara\Tema\Tema AS ModelTema;
use DB;

class MaquilaNacional 
{
	public static function get($start, $end, $colum, $order, $search)
	{
		return DB::table('tbl_orden')->leftJoin('tbl_orden_hoja_trabajo_cabecera', function($join){
											$join->on('tbl_orden.id_hoja_trabajo','tbl_orden_hoja_trabajo_cabecera.id_hoja_trabajo')
												 ->on('tbl_orden.id_tienda_hoja_trabajo','tbl_orden_hoja_trabajo_cabecera.id_tienda_hoja_trabajo');
									})
									->leftJoin('tbl_orden_hoja_trabajo_detalle', function($join){
										$join->on('tbl_orden.id_hoja_trabajo','tbl_orden_hoja_trabajo_detalle.id_hoja_trabajo')
											->on('tbl_orden.id_tienda_hoja_trabajo','tbl_orden_hoja_trabajo_detalle.id_tienda_hoja_trabajo');
									})
									->leftJoin('tbl_contr_item_detalle AS tbl_contr_item_detalle_join', function($join){
										$join->on('tbl_contr_item_detalle_join.id_codigo_contrato', 'tbl_orden_hoja_trabajo_detalle.id_contrato');
										$join->on('tbl_contr_item_detalle_join.id_tienda', 'tbl_orden_hoja_trabajo_detalle.id_tienda_contrato');
										$join->on('tbl_contr_item_detalle_join.id_linea_item_contrato', 'tbl_orden_hoja_trabajo_detalle.id_item_contrato');
									})
									->leftJoin('tbl_sys_estado_tema','tbl_sys_estado_tema.id','tbl_orden.estado')
									->leftJoin('tbl_tienda','tbl_tienda.id','tbl_orden.id_tienda_orden')
									->leftJoin('tbl_prod_categoria_general','tbl_prod_categoria_general.id','tbl_orden_hoja_trabajo_cabecera.categoria')
									->leftJoin('tbl_orden_item', function($join){
										$join->on('tbl_orden_item.id_orden','tbl_orden.id_orden')
											 ->on('tbl_orden_item.id_tienda_orden','tbl_orden.id_tienda_orden');
									})

									->leftJoin('tbl_contr_cabecera', function($join){
										$join->on('tbl_contr_cabecera.codigo_contrato', 'tbl_orden_hoja_trabajo_detalle.id_contrato');
										$join->on('tbl_contr_cabecera.id_tienda_contrato', 'tbl_orden_hoja_trabajo_detalle.id_tienda_contrato');
									})
									->leftJoin('tbl_orden_guardar_items', function($join){
										$join->on('tbl_orden.id_orden', 'tbl_orden_guardar_items.id_orden_guardar');
										$join->on('tbl_orden.id_tienda_orden', 'tbl_orden_guardar_items.id_tienda_contrato');
									})
									->select(
											'tbl_orden.id_orden AS DT_RowId',
											'tbl_orden.id_orden',
											'tbl_tienda.nombre as tienda_orden',
											'tbl_prod_categoria_general.nombre as categoria',
											'tbl_orden.fecha_creacion',
											'tbl_sys_estado_tema.nombre as estado',
											// DB::Raw('FORMAT((SELECT SUM(inventario_producto.precio_compra) FROM tbl_inventario_producto AS inventario_producto INNER JOIN tbl_orden_item AS orden_item ON inventario_producto.id_inventario = orden_item.id_inventario AND inventario_producto.id_tienda_inventario = orden_item.id_tienda_inventario WHERE orden_item.id_orden = tbl_orden_item.id_orden AND orden_item.id_tienda_orden = tbl_orden_item.id_tienda_orden),2,"de_DE") AS valor_contrato'),

											DB::raw("(SELECT DISTINCT GROUP_CONCAT( DISTINCT cod_bolsas_seguridad SEPARATOR ', ') FROM tbl_contr_cabecera INNER JOIN tbl_orden_hoja_trabajo_detalle AS trabajo_detalle ON tbl_contr_cabecera.codigo_contrato = trabajo_detalle.id_contrato AND tbl_contr_cabecera.id_tienda_contrato = trabajo_detalle.id_tienda_contrato WHERE trabajo_detalle.id_tienda_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_tienda_hoja_trabajo AND trabajo_detalle.id_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_hoja_trabajo) AS codigos_bolsas"),
											DB::raw("CONCAT('$ ', FORMAT((SELECT SUM(tbl_contr_item_detalle.precio_ingresado) FROM tbl_contr_item_detalle INNER JOIN tbl_orden_hoja_trabajo_detalle AS trabajo_detalle ON tbl_contr_item_detalle.id_codigo_contrato = trabajo_detalle.id_contrato AND tbl_contr_item_detalle.id_tienda = trabajo_detalle.id_tienda_contrato WHERE trabajo_detalle.id_tienda_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_tienda_hoja_trabajo AND trabajo_detalle.id_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_hoja_trabajo),(SELECT decimales FROM tbl_parametro_general LIMIT 1),'de_DE')) AS valor_contrato"),

											DB::Raw('FORMAT((SELECT SUM(inventario_producto.peso) FROM tbl_inventario_producto AS inventario_producto INNER JOIN tbl_orden_item AS orden_item ON inventario_producto.id_inventario = orden_item.id_inventario AND inventario_producto.id_tienda_inventario = orden_item.id_tienda_inventario WHERE orden_item.id_orden = tbl_orden_item.id_orden AND orden_item.id_tienda_orden = tbl_orden_item.id_tienda_orden),2,"de_DE") AS peso_contrato'),
											DB::RAW('FORMAT((SELECT SUM(orden_item.peso_estimado) FROM tbl_orden_item AS orden_item WHERE tbl_orden_item.id_orden = orden_item.id_orden AND tbl_orden_item.id_tienda_orden = orden_item.id_tienda_orden),2,"de_DE") AS peso_estimado_total'),
											DB::RAW('FORMAT((SELECT SUM(orden_item.peso_total) FROM tbl_orden_item AS orden_item WHERE tbl_orden_item.id_orden = orden_item.id_orden AND tbl_orden_item.id_tienda_orden = orden_item.id_tienda_orden),2,"de_DE") AS peso_total_total'),
											DB::RAW('FORMAT((SELECT SUM(orden_item.peso_taller) FROM tbl_orden_item AS orden_item WHERE tbl_orden_item.id_orden = orden_item.id_orden AND tbl_orden_item.id_tienda_orden = orden_item.id_tienda_orden),2,"de_DE") AS peso_taller_total'),
											DB::RAW('FORMAT((SELECT SUM(orden_item.peso_libre) FROM tbl_orden_guardar_items AS orden_item WHERE tbl_orden_guardar_items.id_orden_guardar = orden_item.id_orden_guardar AND tbl_orden_guardar_items.id_tienda_contrato = orden_item.id_tienda_contrato),2,"de_DE") AS peso_libre_total'),
											DB::RAW('(SELECT COUNT(1) FROM tbl_orden_item AS orden_item WHERE tbl_orden_item.id_orden = orden_item.id_orden AND tbl_orden_item.id_tienda_orden = orden_item.id_tienda_orden) AS cantidad_items')
									)
									->where('tbl_orden.proceso',env('PROCESO_MAQUILA_NACIONAL'))
									->where('tbl_orden.estado',$search["estado"])
									->where(function ($query) use ($search) {
										if($search["id_categoria"] != "")
											$query->where('tbl_prod_categoria_general.id',$search['id_categoria']);
									})
									->orderBy($colum, $order)
									->skip($start)->take($end)
									->distinct()
									->get();
	}

	public static function countMaquilaNacional($start, $end, $colum, $order, $search){

		return DB::table('tbl_orden')->join('tbl_orden_hoja_trabajo_cabecera', function($join){
												$join->on('tbl_orden.id_hoja_trabajo','tbl_orden_hoja_trabajo_cabecera.id_hoja_trabajo')
													->on('tbl_orden.id_tienda_hoja_trabajo','tbl_orden_hoja_trabajo_cabecera.id_tienda_hoja_trabajo');
										})
										->join('tbl_orden_hoja_trabajo_detalle', function($join){
											$join->on('tbl_orden.id_hoja_trabajo','tbl_orden_hoja_trabajo_detalle.id_hoja_trabajo')
												->on('tbl_orden.id_tienda_hoja_trabajo','tbl_orden_hoja_trabajo_detalle.id_tienda_hoja_trabajo');
										})
										->join('tbl_contr_item_detalle AS tbl_contr_item_detalle_join', function($join){
											$join->on('tbl_contr_item_detalle_join.id_codigo_contrato', 'tbl_orden_hoja_trabajo_detalle.id_contrato');
											$join->on('tbl_contr_item_detalle_join.id_tienda', 'tbl_orden_hoja_trabajo_detalle.id_tienda_contrato');
											$join->on('tbl_contr_item_detalle_join.id_linea_item_contrato', 'tbl_orden_hoja_trabajo_detalle.id_item_contrato');
										})
										->join('tbl_sys_estado_tema','tbl_sys_estado_tema.id','tbl_orden.estado')
										->join('tbl_tienda','tbl_tienda.id','tbl_orden.id_tienda_orden')
										->join('tbl_prod_categoria_general','tbl_prod_categoria_general.id','tbl_orden_hoja_trabajo_cabecera.categoria')
										->join('tbl_orden_item', function($join){
											$join->on('tbl_orden_item.id_orden','tbl_orden.id_orden')
												->on('tbl_orden_item.id_tienda_orden','tbl_orden.id_tienda_orden');
										})

										->join('tbl_contr_cabecera', function($join){
											$join->on('tbl_contr_cabecera.codigo_contrato', 'tbl_orden_hoja_trabajo_detalle.id_contrato');
											$join->on('tbl_contr_cabecera.id_tienda_contrato', 'tbl_orden_hoja_trabajo_detalle.id_tienda_contrato');
										})
										->select(
											'tbl_orden.id_orden AS DT_RowId',
											'tbl_orden.id_orden',
											'tbl_tienda.nombre as tienda_orden',
											'tbl_prod_categoria_general.nombre as categoria',
											'tbl_orden.fecha_creacion',
											'tbl_sys_estado_tema.nombre as estado',
											// DB::Raw('FORMAT((SELECT SUM(inventario_producto.precio_compra) FROM tbl_inventario_producto AS inventario_producto INNER JOIN tbl_orden_item AS orden_item ON inventario_producto.id_inventario = orden_item.id_inventario AND inventario_producto.id_tienda_inventario = orden_item.id_tienda_inventario WHERE orden_item.id_orden = tbl_orden_item.id_orden AND orden_item.id_tienda_orden = tbl_orden_item.id_tienda_orden),2,"de_DE") AS valor_contrato'),

											DB::raw("(SELECT DISTINCT GROUP_CONCAT( DISTINCT cod_bolsas_seguridad SEPARATOR ', ') FROM tbl_contr_cabecera INNER JOIN tbl_orden_hoja_trabajo_detalle AS trabajo_detalle ON tbl_contr_cabecera.codigo_contrato = trabajo_detalle.id_contrato AND tbl_contr_cabecera.id_tienda_contrato = trabajo_detalle.id_tienda_contrato WHERE trabajo_detalle.id_tienda_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_tienda_hoja_trabajo AND trabajo_detalle.id_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_hoja_trabajo) AS codigos_bolsas"),
											DB::raw("CONCAT('$ ', FORMAT((SELECT SUM(tbl_contr_item_detalle.precio_ingresado) FROM tbl_contr_item_detalle INNER JOIN tbl_orden_hoja_trabajo_detalle AS trabajo_detalle ON tbl_contr_item_detalle.id_codigo_contrato = trabajo_detalle.id_contrato AND tbl_contr_item_detalle.id_tienda = trabajo_detalle.id_tienda_contrato WHERE trabajo_detalle.id_tienda_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_tienda_hoja_trabajo AND trabajo_detalle.id_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_hoja_trabajo),(SELECT decimales FROM tbl_parametro_general LIMIT 1),'de_DE')) AS valor_contrato"),

											DB::Raw('FORMAT((SELECT SUM(inventario_producto.peso) FROM tbl_inventario_producto AS inventario_producto INNER JOIN tbl_orden_item AS orden_item ON inventario_producto.id_inventario = orden_item.id_inventario AND inventario_producto.id_tienda_inventario = orden_item.id_tienda_inventario WHERE orden_item.id_orden = tbl_orden_item.id_orden AND orden_item.id_tienda_orden = tbl_orden_item.id_tienda_orden),2,"de_DE") AS peso_contrato'),
											DB::RAW('(SELECT SUM(orden_item.peso_estimado) FROM tbl_orden_item AS orden_item WHERE tbl_orden_item.id_orden = orden_item.id_orden AND tbl_orden_item.id_tienda_orden = orden_item.id_tienda_orden) AS peso_estimado_total'),
											DB::RAW('(SELECT SUM(orden_item.peso_total) FROM tbl_orden_item AS orden_item WHERE tbl_orden_item.id_orden = orden_item.id_orden AND tbl_orden_item.id_tienda_orden = orden_item.id_tienda_orden) AS peso_total_total'),
											DB::RAW('(SELECT SUM(orden_item.peso_taller) FROM tbl_orden_item AS orden_item WHERE tbl_orden_item.id_orden = orden_item.id_orden AND tbl_orden_item.id_tienda_orden = orden_item.id_tienda_orden) AS peso_taller_total'),
											DB::RAW('(SELECT COUNT(1) FROM tbl_orden_item AS orden_item WHERE tbl_orden_item.id_orden = orden_item.id_orden AND tbl_orden_item.id_tienda_orden = orden_item.id_tienda_orden) AS cantidad_items')
										)
										->where('tbl_orden.proceso',env('PROCESO_MAQUILA_NACIONAL'))
										->where('tbl_orden.estado',$search["estado"])
										->distinct()
										->count();
	}

	public static function getMaquilaNacionalById($id){
		return ModelMaquilaNacional::where('id',$id)->first();
	}

	public static function procesar($data,$ordenes)
	{
		$result="Actualizado";
		try
		{
			DB::beginTransaction();
			self::process($data,$ordenes);
			DB::commit();	
		}catch(\Exception $e)
		{
			dd($e);
			if($e->getCode() == 23000)
			{
				$result='ErrorUnico';
			}
			else
			{
				$result = 'Error';
			}
		}
		return $result;
	}

	public static function process($data,$ordenes)
	{	
		$fecha = date("Y-m-d H:i:s");
		// dd($ordenes);
		for ($i=0; $i < count($ordenes); $i++) { 
			$secuenciaT = self::sec_trazabilidad($data['id_tienda_orden']);
			DB::table('tbl_orden')->where('id_orden',$ordenes[$i])
									->where('id_tienda_orden',$data['id_tienda_orden'])
									->update([
										'estado' => (int)env('ORDEN_PROCESADA'),
										'mano_obra' => $data['mano_obra'],
										'transporte' => $data['transporte'],
										'costos_indirectos' => $data['costos_indirectos'],
										'otros_costos' => $data['otros_costos']
									]);

			DB::table('tbl_orden_trazabilidad')->where('id_orden',$ordenes[$i])
												->where('id_tienda_orden',$data['id_tienda_orden'])
												->update([
													'actual' => (int)0,
												]);


			DB::table('tbl_orden_trazabilidad')->insert([
												[
													'id_trazabilidad' => (int)$secuenciaT[0]->response,
													'id_tienda_trazabilidad' => (int)$data['id_tienda_orden'], 
													'id_orden' => (int)$ordenes[$i], 
													'id_tienda_orden' => (int)$data['id_tienda_orden'], 
													'actual' => (int)1, 
													'fecha_accion' => $fecha, 
													'accion' => 'Procesado'
												]
			]);	
		}	
		// dd($data);
		for($i=0; $i < count($data['id_item']); $i++)
		{
			DB::table('tbl_orden_item')->where('id_inventario',$data['id_item'][$i])
									->where('id_tienda_inventario',$data['id_tienda_inventario'][$i])
									->update([
										'peso_taller' => $data['peso_taller'][$i],
									]);

			DB::table('tbl_inventario_producto')->where('id_inventario',$data['id_item'][$i])
									->where('id_tienda_inventario',$data['id_tienda_inventario'][$i])
									->update([
										'id_estado_producto' => (int)env('PROCESADO_MAQUILA_NACIONAL'),
									]);
		}

		return true;
	}

	public static function Procesarsubdividir($datosPreparados)
	{
		$result='Insertado';		
		try
		{
			DB::beginTransaction();
			self::crearOrdenes($datosPreparados['CrearOrdenes']);
			self::actualizarAntiguasOrdenes($datosPreparados['AntiguaTrazabilidad']);
			self::crearTrazabilidad($datosPreparados['CrearTrazabilidad']);
			self::actualizarAntiguaTrazabilidad($datosPreparados['AntiguaTrazabilidad']);
			self::ItemsXOrdenesNuevas($datosPreparados['ItemsXOrden']);
			DB::commit();
		}catch(\Exception $e)
		{
			dd($e);
			if($e->getCode() == 23000)
			{
				$result='ErrorUnico';
			}
			else
			{
				$result = 'Error';
			}
			DB::rollback();
		}
		return $result;
	} 

	public static function crearOrdenes($CrearOrdenes)
	{
		 return ModelOrden::insert($CrearOrdenes);
	}
	public static function actualizarAntiguasOrdenes($AntiguaTrazabilidad)
	{
		for ($i=0; $i < count($AntiguaTrazabilidad) ; $i++) 
		{ 
				ModelOrden::where('id_orden',$AntiguaTrazabilidad[$i]['id_orden'])
								->where('id_tienda_orden',$AntiguaTrazabilidad[$i]['id_tienda_orden'])
								->update(['estado' => $AntiguaTrazabilidad[$i]['estado']]);
		}
	}
	public static function crearTrazabilidad($CrearTrazabilidad)
	{
		return DB::table('tbl_orden_trazabilidad')
			  			->insert($CrearTrazabilidad);
	}
	public static function actualizarAntiguaTrazabilidad($AntiguaTrazabilidad)
	{
		for ($i=0; $i < count($AntiguaTrazabilidad) ; $i++) 
		{ 
			DB::table('tbl_orden_trazabilidad')
				->where('id_orden',$AntiguaTrazabilidad[$i]['id_orden'])
				->where('id_tienda_orden',$AntiguaTrazabilidad[$i]['id_tienda_orden'])
				->update(['actual' => $AntiguaTrazabilidad[$i]['actual'], 'accion' => $AntiguaTrazabilidad[$i]['accion']]);
		}
	}
	public static function ItemsXOrdenesNuevas($ItemsXOrden)
	{
		for ($i=0; $i < count($ItemsXOrden) ; $i++) 
		{ 
			DB::table('tbl_orden_item')
					->insert($ItemsXOrden[$i]);
		}
	}

	public static function sec_trazabilidad($id_tienda)
    {
        return DB::select('CALL sp_secuencias_tienda_x (?,?,?)',array($id_tienda,(int)27,(int)1));
    }

	public static function getSelectList()
	{
		return ModelMaquilaNacional::select('id','nombre AS name')->where('estado','1')->get();
	}

	public static function getListProceso(){
		return ModelTema::select(
								'id',
								'nombre AS name'
								)
								->whereIn('id',[env('PROCESO_VITRINA'),
														env('PROCESO_FUNDICION'),
														env('PROCESO_MAQUILA'),
														env('PROCESO_JOYA_ESPECIAL')
														])
								->get();
	}

	public static function getListProcesoByVitrina(){
		return ModelTema::select(
								'id',
								'nombre AS name'
								)
								->whereIn('id',[env('PROCESO_VITRINA')])
								->get();
	}

	public static function getItemOrden($id_tienda,$id)
	{
		return DB::table('tbl_orden_hoja_trabajo_cabecera')
										->join('tbl_orden', function($join){
											$join->on('tbl_orden.id_hoja_trabajo','tbl_orden_hoja_trabajo_cabecera.id_hoja_trabajo')
												 ->on('tbl_orden.id_tienda_hoja_trabajo','tbl_orden_hoja_trabajo_cabecera.id_tienda_hoja_trabajo');
										})
										
										->join('tbl_orden_hoja_trabajo_detalle', function($join){
                                            $join->on('tbl_orden.id_hoja_trabajo','tbl_orden_hoja_trabajo_detalle.id_hoja_trabajo')
                                                ->on('tbl_orden.id_tienda_hoja_trabajo','tbl_orden_hoja_trabajo_detalle.id_tienda_hoja_trabajo');
                                        })
										->join('tbl_orden_item',function($join){
											$join->on('tbl_orden_item.id_orden','tbl_orden.id_orden')
												 ->on('tbl_orden_item.id_tienda_orden','tbl_orden.id_tienda_orden');
										})
										->leftjoin('tbl_cliente', function($join)
										{
											$join->on('tbl_cliente.codigo_cliente','=','tbl_orden.id_cliente');	
											$join->on('tbl_cliente.id_tienda','=','tbl_orden.id_tienda_cliente');	
										})
										->join('tbl_inventario_item_contrato', function($join){
											$join->on('tbl_inventario_item_contrato.id_inventario','tbl_orden_item.id_inventario')
												 ->on('tbl_inventario_item_contrato.id_tienda_inventario','tbl_orden_item.id_tienda_inventario');
										})
										->join('tbl_tienda', function($join){
												$join->on('tbl_inventario_item_contrato.id_tienda_inventario','tbl_tienda.id');
										})
										->join('tbl_contr_item_detalle', function($join){
											$join->on('tbl_contr_item_detalle.id_codigo_contrato','tbl_inventario_item_contrato.id_contrato')
												 ->on('tbl_contr_item_detalle.id_tienda','tbl_inventario_item_contrato.id_tienda_contrato')
												 ->on('tbl_contr_item_detalle.id_linea_item_contrato','tbl_inventario_item_contrato.id_item_contrato');
										})
										->join('tbl_contr_cabecera', function($join){
											$join->on('tbl_contr_cabecera.codigo_contrato','tbl_contr_item_detalle.id_codigo_contrato')
												 ->on('tbl_contr_cabecera.id_tienda_contrato','tbl_contr_item_detalle.id_tienda');
										})
										->join('tbl_prod_categoria_general','tbl_prod_categoria_general.id','tbl_orden_hoja_trabajo_cabecera.categoria')
										
										->leftJoin('tbl_orden_guardar', function($join){
											$join->on('tbl_orden_guardar.id_orden','tbl_orden.id_orden')
												 ->on('tbl_orden_guardar.id_tienda','tbl_orden.id_tienda_orden');
										})
										->leftJoin('tbl_orden_guardar_items', function($join){
											$join->on('tbl_orden_guardar_items.id_orden_guardar','tbl_orden_item.id_orden')
											->on('tbl_orden_guardar_items.id_tienda_contrato','tbl_orden_item.id_tienda_orden')
											->on('tbl_orden_guardar_items.id_inventario','tbl_orden_item.id_inventario');
										})
										->where('tbl_orden.id_orden',$id)
										->where('tbl_orden.id_tienda_orden',$id_tienda)
										// ->where('tbl_orden.proceso','7')
									   	->select(
										    	'tbl_inventario_item_contrato.id_contrato',
												'tbl_inventario_item_contrato.id_tienda_contrato as tienda_contrato',
												'tbl_orden.fecha_creacion',
												'tbl_inventario_item_contrato.id_inventario',
												'tbl_inventario_item_contrato.id_tienda_inventario',
												'tbl_inventario_item_contrato.id_item_contrato AS id_item',
												'tbl_contr_item_detalle.nombre',
												'tbl_contr_item_detalle.observaciones',
												'tbl_contr_item_detalle.id_linea_item_contrato AS linea_item',
												'tbl_tienda.nombre AS nombre_tienda_contrato',
												DB::Raw("FORMAT((tbl_contr_item_detalle.peso_estimado),2,'de_DE') AS peso_estimado"),
												DB::Raw("FORMAT((tbl_contr_item_detalle.peso_total),2,'de_DE') AS peso_total"),
												DB::Raw("FORMAT((tbl_orden_item.peso_taller),2,'de_DE') AS peso_taller"),
												DB::Raw("tbl_contr_item_detalle.precio_ingresado AS precio_ingresado_noformat"),
												DB::Raw("tbl_contr_item_detalle.peso_estimado AS peso_estimado_noformat"),
												DB::Raw("tbl_contr_item_detalle.peso_total AS peso_total_noformat"),
												DB::raw("CONCAT('$ ', FORMAT((tbl_contr_item_detalle.precio_ingresado),(SELECT decimales FROM tbl_parametro_general LIMIT 1),'de_DE')) AS precio_ingresado"),
												DB::raw("CONCAT('$ ', FORMAT((select SUM(precio_ingresado) FROM tbl_contr_item_detalle WHERE tbl_contr_item_detalle.id_codigo_contrato = tbl_inventario_item_contrato.id_contrato AND tbl_contr_item_detalle.id_tienda = tbl_inventario_item_contrato.id_tienda_contrato),(SELECT decimales FROM tbl_parametro_general LIMIT 1),'de_DE')) AS Suma_contrato"),               
                                                DB::raw("(SELECT DISTINCT GROUP_CONCAT( DISTINCT cod_bolsas_seguridad SEPARATOR ', ') FROM tbl_contr_cabecera INNER JOIN tbl_orden_hoja_trabajo_detalle AS trabajo_detalle ON tbl_contr_cabecera.codigo_contrato = trabajo_detalle.id_contrato AND tbl_contr_cabecera.id_tienda_contrato = trabajo_detalle.id_tienda_contrato WHERE trabajo_detalle.id_tienda_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_tienda_hoja_trabajo AND trabajo_detalle.id_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_hoja_trabajo) AS Bolsas"),
												DB::raw('cod_bolsa_seguridad_hasta - cod_bolsa_seguridad_desde AS Bolsas'),
												'tbl_cliente.numero_documento AS destinatario',
												'tbl_orden_hoja_trabajo_cabecera.id_hoja_trabajo',
												'tbl_orden_hoja_trabajo_cabecera.id_tienda_hoja_trabajo',
												'tbl_prod_categoria_general.nombre as categoria',
												'tbl_prod_categoria_general.id as id_categoria',
												'tbl_orden.id_orden',
												'tbl_orden.id_tienda_orden',
												'tbl_orden_item.id_orden_item',
												'tbl_orden_item.id_tienda_orden_item',

												'tbl_orden_guardar_items.peso_taller',
												'tbl_orden_guardar_items.peso_libre',
												'tbl_orden_guardar_items.id_proceso',
												'tbl_orden_guardar.abre_bolsa',

												DB::Raw("LPAD(tbl_contr_cabecera.codigo_contrato,6,'0') AS codigo_contrato"),
												DB::Raw("CONCAT(COALESCE(tbl_cliente.nombres,''), ' ', COALESCE(tbl_cliente.primer_apellido,''), ' ',  COALESCE(tbl_cliente.segundo_apellido,'')) as nombres_cliente"),
												DB::raw("REPLACE(FORMAT(tbl_cliente.numero_documento,0), ',', '.') AS numero_documento_cliente"),
												DB::Raw("YEAR(tbl_contr_cabecera.fecha_creacion) AS anho_contrato"),
												DB::Raw("MONTH(tbl_contr_cabecera.fecha_creacion) AS mes_contrato"),
												DB::Raw("(tbl_contr_cabecera.fecha_creacion) AS fecha_contrato"),
												DB::Raw("DAY(tbl_contr_cabecera.fecha_creacion) AS dia_contrato")
										   )
										   ->distinct()
									   	->get();
	}

	public static function getItemOrdenConcat($id_tienda,$id)
	{
		return DB::table('tbl_orden_hoja_trabajo_cabecera')
										->join('tbl_orden', function($join){
											$join->on('tbl_orden.id_hoja_trabajo','tbl_orden_hoja_trabajo_cabecera.id_hoja_trabajo')
												 ->on('tbl_orden.id_tienda_hoja_trabajo','tbl_orden_hoja_trabajo_cabecera.id_tienda_hoja_trabajo');
										})
										
										->join('tbl_orden_hoja_trabajo_detalle', function($join){
                                            $join->on('tbl_orden.id_hoja_trabajo','tbl_orden_hoja_trabajo_detalle.id_hoja_trabajo')
                                                ->on('tbl_orden.id_tienda_hoja_trabajo','tbl_orden_hoja_trabajo_detalle.id_tienda_hoja_trabajo');
                                        })
										->join('tbl_orden_item',function($join){
											$join->on('tbl_orden_item.id_orden','tbl_orden.id_orden')
												 ->on('tbl_orden_item.id_tienda_orden','tbl_orden.id_tienda_orden');
										})
										->leftjoin('tbl_cliente', function($join)
										{
											$join->on('tbl_cliente.codigo_cliente','=','tbl_orden.id_cliente');	
											$join->on('tbl_cliente.id_tienda','=','tbl_orden.id_tienda_cliente');	
										})
										->join('tbl_inventario_item_contrato', function($join){
											$join->on('tbl_inventario_item_contrato.id_inventario','tbl_orden_item.id_inventario')
												 ->on('tbl_inventario_item_contrato.id_tienda_inventario','tbl_orden_item.id_tienda_inventario');
										})
										->join('tbl_tienda', function($join){
												$join->on('tbl_inventario_item_contrato.id_tienda_inventario','tbl_tienda.id');
										})
										->join('tbl_contr_item_detalle', function($join){
											$join->on('tbl_contr_item_detalle.id_codigo_contrato','tbl_inventario_item_contrato.id_contrato')
												 ->on('tbl_contr_item_detalle.id_tienda','tbl_inventario_item_contrato.id_tienda_contrato')
												 ->on('tbl_contr_item_detalle.id_linea_item_contrato','tbl_inventario_item_contrato.id_item_contrato');
										})
										->join('tbl_contr_cabecera', function($join){
											$join->on('tbl_contr_cabecera.codigo_contrato','tbl_contr_item_detalle.id_codigo_contrato')
												 ->on('tbl_contr_cabecera.id_tienda_contrato','tbl_contr_item_detalle.id_tienda');
										})
										->leftJoin('tbl_prod_categoria_general','tbl_prod_categoria_general.id','tbl_orden_hoja_trabajo_cabecera.categoria')
										
										->leftJoin('tbl_orden_guardar', function($join){
											$join->on('tbl_orden_guardar.id_orden','tbl_orden.id_orden')
												 ->on('tbl_orden_guardar.id_tienda','tbl_orden.id_tienda_orden');
										})
										->leftJoin('tbl_orden_guardar_items', function($join){
											$join->on('tbl_orden_guardar_items.id_orden_guardar','tbl_orden_item.id_orden')
											->on('tbl_orden_guardar_items.id_tienda_contrato','tbl_orden_item.id_tienda_orden')
											->on('tbl_orden_guardar_items.id_inventario','tbl_orden_item.id_inventario');
										})
										->where('tbl_orden.id_orden',$id)
										->where('tbl_orden.id_tienda_orden',$id_tienda)
										// ->where('tbl_orden.proceso','7')
									   	->select(
												DB::raw("CONCAT(tbl_inventario_item_contrato.id_tienda_inventario,'-',tbl_inventario_item_contrato.id_inventario,'-',tbl_inventario_item_contrato.id_contrato) AS inventario"),
												"tbl_orden.id_orden",
												"tbl_orden.id_tienda_orden"
										)
										->distinct()
									   	->get();
	}

	public static function getDestinatariosOrden($id_tienda,$id)
	{
		return DB::table('tbl_orden_guardar')
				->join('tbl_orden_guardar_destinatarios','tbl_orden_guardar.id_orden','tbl_orden_guardar_destinatarios.id_orden_guardar')
				->leftJoin('tbl_sys_tema','tbl_sys_tema.id','tbl_orden_guardar_destinatarios.id_proceso')
				->select(
						'tbl_sys_tema.nombre as proceso',
						'tbl_orden_guardar_destinatarios.destinatario',
						'tbl_orden_guardar_destinatarios.codigo_verificacion',
						'tbl_sys_tema.id as id_proceso',
						'tbl_orden_guardar_destinatarios.numero_bolsa'
				)
				->where('tbl_orden_guardar.id_tienda',$id_tienda)
				->where('tbl_orden_guardar.id_orden',$id)
				->get();
	}

	public static function getDestinatariosOrdenPadre($id_tienda,$id)
	{
		return DB::table('tbl_orden_destinatario')
				->join('tbl_orden', function($join){
					$join->on('tbl_orden.id_orden','tbl_orden_destinatario.id_orden')
						 ->on('tbl_orden.id_tienda_orden','tbl_orden_destinatario.id_tienda_orden');
				})
				->leftJoin('tbl_sys_tema','tbl_sys_tema.id','tbl_orden_destinatario.id_proceso')
				->leftJoin('tbl_cliente','tbl_cliente.numero_documento','tbl_orden_destinatario.destinatario')
				->leftJoin('tbl_clie_suc_cliente', function($join){
					$join->on('tbl_clie_suc_cliente.id_cliente','tbl_cliente.codigo_cliente')
						->on('tbl_clie_suc_cliente.id_tienda_cliente','tbl_cliente.id_tienda')
						->on('tbl_clie_suc_cliente.id_sucursal','tbl_orden_destinatario.sucursal');
				})
				->leftJoin('tbl_ciudad AS ciudad_cliente','tbl_cliente.id_ciudad_residencia','ciudad_cliente.id')
				->leftJoin('tbl_ciudad AS ciudad_sucursal','tbl_clie_suc_cliente.id_ciudad','ciudad_sucursal.id')
				->select(
						'tbl_sys_tema.id as id_proceso',
						'tbl_sys_tema.nombre as proceso',
						'tbl_orden_destinatario.destinatario',
						'tbl_orden_destinatario.codigo_verificacion',
						'tbl_orden_destinatario.numero_bolsa',
						'tbl_orden.fecha_creacion',
						'tbl_cliente.nombres AS nombres_destinatario',
						DB::raw("IF(tbl_clie_suc_cliente.id_sucursal IS NULL, 'ÚNICA SUCURSAL', tbl_clie_suc_cliente.nombre) AS sucursal"),
						DB::raw("IF(tbl_clie_suc_cliente.id_sucursal IS NULL, tbl_cliente.telefono_residencia, tbl_clie_suc_cliente.telefono_sucursal) AS telefono"),
						DB::raw("IF(tbl_clie_suc_cliente.id_sucursal IS NULL, ciudad_cliente.nombre, ciudad_sucursal.nombre) AS ciudad")
				)
				->where('tbl_orden_destinatario.id_tienda_orden',$id_tienda)
				->where('tbl_orden_destinatario.id_orden',$id)
				->get();
	}
 
	public static function getTiendaByIp($ip){
		return ModelTienda::select('id', 'nombre')->where('ip_fija', $ip)->first();
	}

	public static function validarItem($id_tienda_inventario,$id_inventario,$id_contrato)
	{
		return DB::table('tbl_orden_item')->join('tbl_orden',function($join){
											$join->on('tbl_orden.id_orden','tbl_orden_item.id_orden')
												 ->on('tbl_orden.id_tienda_orden','tbl_orden_item.id_tienda_orden');
										})
										->join('tbl_inventario_item_contrato',function($join){
											$join->on('tbl_inventario_item_contrato.id_inventario','tbl_orden_item.id_inventario')
												 ->on('tbl_inventario_item_contrato.id_tienda_inventario','tbl_orden_item.id_tienda_inventario');
										})
										->join('tbl_orden_trazabilidad',function($join){
											$join->on('tbl_orden_trazabilidad.id_orden','tbl_orden.id_orden')
												 ->on('tbl_orden_trazabilidad.id_tienda_orden','tbl_orden.id_tienda_orden');
										})
										->join('tbl_tienda','tbl_tienda.id','tbl_orden_item.id_tienda_inventario')
										->join('tbl_sys_estado_tema','tbl_sys_estado_tema.id','tbl_orden.estado')
										->where('tbl_orden_item.id_tienda_inventario',$id_tienda_inventario)
										->where('tbl_orden_trazabilidad.actual',1)
										->where('tbl_inventario_item_contrato.id_contrato',$id_contrato)
										->select(
													'tbl_orden_item.id_orden',
													'tbl_inventario_item_contrato.id_contrato',
													'tbl_inventario_item_contrato.id_inventario',
													'tbl_inventario_item_contrato.id_tienda_inventario',
													'tbl_orden.estado',
													'tbl_tienda.nombre as tienda',
													'tbl_sys_estado_tema.nombre as nombre_estado'
												)
										->get();
	}

	public static function validarProcesos($id_inventario,$id_tienda_inventario,$id_contrato)
	{

		return DB::table('tbl_orden_item')->join('tbl_orden',function($join){
											$join->on('tbl_orden.id_orden','tbl_orden_item.id_orden')
												 ->on('tbl_orden.id_tienda_orden','tbl_orden_item.id_tienda_orden');
										})
										->join('tbl_inventario_item_contrato',function($join){
											$join->on('tbl_inventario_item_contrato.id_inventario','tbl_orden_item.id_inventario')
												 ->on('tbl_inventario_item_contrato.id_tienda_inventario','tbl_orden_item.id_tienda_inventario');
										})
										->whereIn('tbl_orden_item.id_inventario',$id_inventario)
										->whereIn('tbl_orden.estado',[env('PROCESADO_MAQUILA_NACIONAL'),env('PROCESADO_REFACCION'),env('PROCESADO_FUNDICION'),env('PROCESADO_VITRINA'),env('PROCESADO_MAQUILA'),env('PROCESADO_JOYA_ES')])
										->where('tbl_orden_item.id_tienda_inventario',$id_tienda_inventario)
										->where('tbl_inventario_item_contrato.id_contrato',$id_contrato)
										->select('tbl_orden_item.id_orden')
										->first();
	}

	public static function quitarItems($id_inventario,$id_tienda_inventario,$id_contrato)
	{
		$resultado = "exito";
		try{
			DB::beginTransaction();
			self::updateContrato($id_tienda_inventario,$id_contrato);
			self::deleteInventario($id_inventario,$id_tienda_inventario);
			self::deleteItemHojaTrabajo($id_tienda_inventario,$id_contrato);
			self::deleteItemContratoInv($id_inventario,$id_tienda_inventario);
			self::deleteOrdenItem($id_inventario,$id_tienda_inventario);
			DB::commit();
		}catch(\Exception $e){
			dd($e);
			if($e->getCode() == 23000)
			{
				$resultado='ErrorUnico';
			}
			else
			{
				$resultado = 'error_quitar';
			}
		}
		return $resultado;
	}

	public static function updateContrato($id_tienda_contrato,$id_contrato)
	{
		return DB::table('tbl_contr_cabecera')->where('id_tienda_contrato',$id_tienda_contrato)
									   		->where('codigo_contrato',$id_contrato)
									   		->update(['id_estado_contrato' => env('ESTADO_CONTRATO_RESTABLECER')]);
	}

	public static function deleteInventario($id_inventario,$id_tienda_inventario)
	{
		return DB::table('tbl_inventario_producto')->whereIn('id_inventario',$id_inventario)
												->where('id_tienda_inventario',$id_tienda_inventario)
												->delete();
	}

	public static function deleteItemContratoInv($id_inventario,$id_tienda_inventario)
	{
		return DB::table('tbl_inventario_item_contrato')->whereIn('id_inventario',$id_inventario)
												->where('id_tienda_inventario',$id_tienda_inventario)
												->delete();
	}

	public static function deleteOrdenItem($id_inventario,$id_tienda_inventario)
	{
		return DB::table('tbl_inventario_item_contrato')->whereIn('id_inventario',$id_inventario)
												->where('id_tienda_inventario',$id_tienda_inventario)
												->delete();
	}

	public static function deleteItemHojaTrabajo($id_tienda_contrato,$id_contrato)
	{
		return DB::table('tbl_orden_hoja_trabajo_detalle')->where('id_contrato',$id_contrato)
												->where('id_tienda_contrato',$id_tienda_contrato)
												->delete();
	}

	public static function updateEstadoInventario($id_inventario,$id_tienda,$motivo,$estado)
	{
		return DB::table('tbl_inventario_producto')->where('id_inventario',$id_inventario)
												   ->where('id_tienda_inventario',$id_tienda)
												   ->update([
													   'id_estado_producto' => $estado,
													   'id_motivo_producto' => $motivo
												   ]);
	}

	public static function getOrdenExcel($id_orden, $id_tienda)
	{
		return DB::table('tbl_orden')->join('tbl_orden_hoja_trabajo_cabecera', function($join){
						$join->on('tbl_orden.id_hoja_trabajo','tbl_orden_hoja_trabajo_cabecera.id_hoja_trabajo')
							->on('tbl_orden.id_tienda_hoja_trabajo','tbl_orden_hoja_trabajo_cabecera.id_tienda_hoja_trabajo');
				})
				->join('tbl_orden_hoja_trabajo_detalle', function($join){
					$join->on('tbl_orden.id_hoja_trabajo','tbl_orden_hoja_trabajo_detalle.id_hoja_trabajo')
						->on('tbl_orden.id_tienda_hoja_trabajo','tbl_orden_hoja_trabajo_detalle.id_tienda_hoja_trabajo');
				})
				->join('tbl_contr_item_detalle AS tbl_contr_item_detalle_join', function($join){
					$join->on('tbl_contr_item_detalle_join.id_codigo_contrato', 'tbl_orden_hoja_trabajo_detalle.id_contrato');
					$join->on('tbl_contr_item_detalle_join.id_tienda', 'tbl_orden_hoja_trabajo_detalle.id_tienda_contrato');
					$join->on('tbl_contr_item_detalle_join.id_linea_item_contrato', 'tbl_orden_hoja_trabajo_detalle.id_item_contrato');
				})
				->join('tbl_sys_estado_tema','tbl_sys_estado_tema.id','tbl_orden.estado')
				->join('tbl_tienda','tbl_tienda.id','tbl_orden.id_tienda_orden')
				->join('tbl_prod_categoria_general','tbl_prod_categoria_general.id','tbl_orden_hoja_trabajo_cabecera.categoria')
				->join('tbl_orden_item', function($join){
					$join->on('tbl_orden_item.id_orden','tbl_orden.id_orden')
						->on('tbl_orden_item.id_tienda_orden','tbl_orden.id_tienda_orden');
				})
				->join('tbl_inventario_producto', function($join){
					$join->on('tbl_orden_item.id_inventario','tbl_inventario_producto.id_inventario')
						->on('tbl_orden_item.id_tienda_orden','tbl_inventario_producto.id_tienda_inventario');
				})

				->leftJoin('tbl_prod_catalogo', function($join){
					$join->on('tbl_prod_catalogo.id','tbl_inventario_producto.id_catalogo_producto');
				})

				->join('tbl_contr_cabecera', function($join){
					$join->on('tbl_contr_cabecera.codigo_contrato', 'tbl_orden_hoja_trabajo_detalle.id_contrato');
					$join->on('tbl_contr_cabecera.id_tienda_contrato', 'tbl_orden_hoja_trabajo_detalle.id_tienda_contrato');
				})
				->select(
						'tbl_orden.id_orden AS DT_RowId',
						'tbl_tienda.nombre as tienda_orden',
						'tbl_prod_categoria_general.nombre as categoria',
						'tbl_contr_cabecera.codigo_contrato',
						'tbl_contr_cabecera.fecha_creacion AS fecha_perfeccionamiento',
						'tbl_contr_cabecera.fecha_creacion AS fecha_contratacion',
						'tbl_orden_item.id_inventario',
						'tbl_prod_catalogo.descripcion AS nombre',
						'tbl_prod_catalogo.descripcion AS observaciones',
						'tbl_inventario_producto.peso AS peso_total',
						'tbl_inventario_producto.peso_estimado',
						'tbl_inventario_producto.precio_compra',
						DB::raw("CONCAT('$ ', FORMAT((SELECT SUM(tbl_contr_item_detalle.precio_ingresado) FROM tbl_contr_item_detalle INNER JOIN tbl_orden_hoja_trabajo_detalle AS trabajo_detalle ON tbl_contr_item_detalle.id_codigo_contrato = trabajo_detalle.id_contrato AND tbl_contr_item_detalle.id_tienda = trabajo_detalle.id_tienda_contrato WHERE trabajo_detalle.id_tienda_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_tienda_hoja_trabajo AND trabajo_detalle.id_hoja_trabajo = tbl_orden_hoja_trabajo_detalle.id_hoja_trabajo),(SELECT decimales FROM tbl_parametro_general LIMIT 1),'de_DE')) AS Suma_contrato")
				)
				->distinct()
				->where('tbl_orden.id_orden',$id_orden)
				->where('tbl_orden.id_tienda_orden',$id_tienda)
				->get();
	}

	// Función para anular orden
	// public static function AnularOrden($id_orden, $id_tienda_orden)
	// {
	// 	return DB::table('tbl_orden')->where('id_orden',$id_orden)
	// 											   ->where('id_tienda_orden',$id_tienda_orden)
	// 											   ->update([
	// 												   'estado' => 0
	// 											   ]);
	// }

	public static function AnularOrden($id_orden, $id_tienda_orden, $id_orden_padre)
	{
		DB::beginTransaction();
		$ordenes = self::getOrdenesTraza($id_orden_padre, $id_tienda_orden);
		self::deleteOrdenItemTraza($ordenes, $id_tienda_orden);
		self::deleteTraza($id_orden_padre, $id_tienda_orden);
		self::activeTrazaAntigua($id_orden_padre, $id_tienda_orden);
		self::anularOrdenesTraza($ordenes, $id_tienda_orden);
		self::activeOrdenTrazaAntigua($id_orden_padre, $id_tienda_orden);
		DB::commit();
	}

	public static function getOrdenesTraza($id_orden_padre, $id_tienda_orden)
	{
		$ordenes = DB::table('tbl_orden_trazabilidad')
		->select('tbl_orden.id_orden')
		->join('tbl_orden', function($join){
			$join->on('tbl_orden.id_orden', 'tbl_orden_trazabilidad.id_orden');
			$join->on('tbl_orden.id_tienda_orden', 'tbl_orden_trazabilidad.id_tienda_orden');
		})
		->where('id_traza_padre',$id_orden_padre)
		->where('id_tienda_traza_padre',$id_tienda_orden)
		->get();

		$ordenes_array = array();
		for ($i=0; $i < count($ordenes); $i++) { 
			array_push($ordenes_array, $ordenes[$i]->id_orden);
		}
		return $ordenes_array;
	}

	public static function deleteOrdenItemTraza($ordenes, $id_tienda_orden)
	{
		DB::table('tbl_orden_item')
		->whereIn('id_orden', $ordenes)
		->where('id_tienda_orden', $id_tienda_orden)->delete();
	}

	public static function deleteTraza($id_orden_padre, $id_tienda_orden)
	{
		DB::table('tbl_orden_trazabilidad')
		->where('id_traza_padre', $id_orden_padre)
		->where('id_tienda_traza_padre', $id_tienda_orden)->delete();
	}

	public static function activeTrazaAntigua($id_orden_padre, $id_tienda_orden)
	{
		DB::table('tbl_orden_trazabilidad')
		->where('id_orden', $id_orden_padre)
		->where('id_tienda_traza_padre', $id_tienda_orden)
		->update(['actual' => 1, 'accion' => 'Creado']);
	}

	public static function anularOrdenesTraza($ordenes, $id_tienda_orden)
	{
		DB::table('tbl_orden')
		->whereIn('id_orden', $ordenes)
		->where('id_tienda_orden', $id_tienda_orden)
		->update(['estado' => env('ORDEN_ANULADA')]);
	}
	
	public static function activeOrdenTrazaAntigua($id_orden_padre, $id_tienda_orden)
	{
		DB::table('tbl_orden')
		->where('id_orden', $id_orden_padre)
		->where('id_tienda_orden', $id_tienda_orden)
		->update(['estado' => env('ORDEN_PENDIENTE_POR_PROCESAR')]);
	}

	public static function getIdOrdenPadre($id_orden, $id_tienda_orden)
	{
		return DB::table('tbl_orden_trazabilidad')
			->where('id_orden',$id_orden)
			->where('id_tienda_orden',$id_tienda_orden)
			->value('id_traza_padre');
	}

	public static function countOrdenesProcesadas($id_orden_padre, $id_tienda_ordenes)
	{
		return DB::table('tbl_orden_trazabilidad')
			->join('tbl_orden', function($join){
				$join->on('tbl_orden.id_orden', 'tbl_orden_trazabilidad.id_orden');
				$join->on('tbl_orden.id_tienda_orden', 'tbl_orden_trazabilidad.id_tienda_orden');
			})
			->where('id_traza_padre',$id_orden_padre)
			->where('id_tienda_traza_padre',$id_tienda_ordenes)
			->where('estado',env('ORDEN_PROCESADA'))
			->count();
	}

	public static function datosPerfeccionamiento($id_tienda, $id_contrato)
	{
		// dd($id_contrato);
		return (DB::table('tbl_orden_guardar_items')
			->join('tbl_orden_guardar', function($join){
				$join->on('tbl_orden_guardar_items.id_orden_guardar', 'tbl_orden_guardar.id');
				$join->on('tbl_orden_guardar_items.id_tienda_contrato', 'tbl_orden_guardar.id_tienda');
			})
			->select(
				'fecha_creacion',
				'id_orden'
			)
			->where('tbl_orden_guardar_items.codigo_contrato',$id_contrato)
			->where('tbl_orden_guardar_items.id_tienda_contrato',$id_tienda)
			->first());
	}

	public static function getPorcentTolerancia(){
		return DB::table('tbl_parametro_general')->value('porcentaje_tolerancia');
	}

	public static function getReferencias($id_categoria){
		return DB::table('tbl_prod_catalogo')
		->select('id', 'nombre')
		->where('id_categoria', $id_categoria)
		->get();
	}

	public static function transformacionglobalProcesar($data, $request){
		$result = true;
		try{
			DB::table('tbl_inventario_producto')->insert($data);
			DB::table('tbl_orden')
				->where('id_orden', $request->id_orden)
				->where('id_tienda_orden', $request->id_tienda_orden)
				->update(['estado' => env('MAQUILA_NACIONAL_TRANSFORMACION_GLOBAL')]);
		}catch(Exception $ex){
			$result = false;
		}
		return $result;
	}
	
}