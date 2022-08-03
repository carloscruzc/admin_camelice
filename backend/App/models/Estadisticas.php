<?php
namespace App\models;
defined("APPPATH") OR die("Access denied");

use \Core\Database;
use \Core\MasterDom;
use \App\interfaces\Crud;
use \App\controllers\UtileriasLog;

class Estadisticas implements Crud{
    public static function getAll(){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ua.id_registrado,ua.nombre,ua.apellidop,ua.apellidom,ua.codigo_beca,ua.clave_socio,ig.fecha_hora
      FROM registrados ua
      INNER JOIN impresion_gafete ig on(ua.id_registrado = ig.id_registrado)
      GROUP BY ua.id_registrado
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getAllConstancias(){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ua.id_registrado,ua.nombre,ua.apellidop,ua.apellidom,ua.codigo_beca,ua.clave_socio,ig.fecha_descarga
      FROM registrados ua
      INNER JOIN impresion_constancia ig ON (ua.id_registrado = ig.user_id);
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getAllSocioActivos(){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT COUNT(*) as c,pp.id_registrado,ua.nombre,ua.apellidop,ua.apellidom,ua.email,pp.status,ua.fecha_registro
      FROM pendiente_pago pp
      INNER JOIN registrados ua ON(pp.id_registrado = ua.id_registrado)
      WHERE pp.id_producto IN (2,3,4,5,6) and pp.status = 1 GROUP BY ua.id_registrado HAVING c > 4;
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getDataCaja(){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ua.nombre,ua.apellidop,ua.apellidom,tc.productos,tc.total_pesos,tc.fecha_transaccion
      FROM registrados ua
      INNER JOIN transaccion_compra tc ON (ua.id_registrado = tc.id_registrado);
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getDataCajaByFecha($date){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT tc.id_transaccion_compra,ua.nombre,ua.apellidop,ua.apellidom,tc.productos,tc.total_pesos,tc.fecha_transaccion
      FROM registrados ua
      INNER JOIN transaccion_compra tc ON (ua.id_registrado = tc.id_registrado)
      WHERE fecha_transaccion LIKE '%$date%';
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getById($id){
         
    }

    public static function deleteProducto($id){
      $mysqli = Database::getInstance(true);
      $query =<<<sql
      UPDATE productos SET status = 0 WHERE id_producto = $id
sql;

      return $mysqli->update($query);
    }

    
    public static function getProductos(){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT * FROM productos
sql;
      return $mysqli->queryAll($query);
        
    }
    
    public static function insert($data){
        $mysqli = Database::getInstance(1);


        if($data->_tipo == "es_curso"){
          $tipo = 'es_curso';
        }else if($data->_tipo == "es_servicio"){
          $tipo = 'es_servicio';
        }
        $query=<<<sql
            INSERT INTO productos(clave, nombre, fecha_producto, descripcion, $tipo, precio_publico,tipo_moneda, max_compra, status)
            VALUES(:clave, :nombre, NOW(),:descripcion, 1, :precio_publico, 'MXN',1, 1);
sql;


            $parametros = array(
            
            ':clave'=>$data->_clave,
            ':nombre'=>$data->_nombre,
            ':descripcion'=>$data->_descripcion,
            ':precio_publico'=>$data->_precio           
            );
 
            $id = $mysqli->insert($query,$parametros);
            //UtileriasLog::addAccion($accion);
            return $id;
         
    }
    public static function update($data){
        
    }
    public static function delete($id){
        
    }
    public static function getNumAsistencias(){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT COUNT(*) AS total FROM asistencias
sql;
      return $mysqli->queryOne($query);
    }
} 