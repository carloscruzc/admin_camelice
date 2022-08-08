<?php
namespace App\models;
defined("APPPATH") OR die("Access denied");

use \Core\Database;
use \App\interfaces\Crud;
use \App\controllers\UtileriasLog;

class Asistentes{

    public static function getAll(){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ra.nombre as nombre_usuario, ra.apellido_paterno, ra.apellido_materno, 
      ra.id_registro_acceso, ra.clave, ua.status as status_user, ra.email AS correo_electronico, 
      ch.nombre_categoria, uad.nombre as nombre_administrador
      FROM registros_acceso ra
      INNER JOIN utilerias_asistentes ua ON (ra.id_registro_acceso = ua.id_registro_acceso) 
      INNER JOIN habitaciones_hotel hh ON (ra.id_habitacion = hh.id_habitacion) 
      INNER JOIN categorias_habitaciones ch ON (ch.id_categoria_habitacion = hh.id_categoria_habitacion)
      INNER JOIN utilerias_administradores uad ON (hh.utilerias_administradores_id = uad.utilerias_administradores_id)
      WHERE ra.id_registro_acceso = ua.id_registro_acceso
      and ra.politica = 1 and ua.status = 1 
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getAllRegister(){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT * FROM registros_acceso WHERE politica = 1
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getAllRegisterSinHabitacion(){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ra.id_registro_acceso, CONCAT(ra.nombre, ' ', ra.segundo_nombre, ' ', ra.apellido_paterno, ' ',ra.apellido_materno, ' - ',ra.email,'') as nombre
      FROM registros_acceso ra
      WHERE ra.id_registro_acceso NOT IN (SELECT id_registro_acceso FROM asigna_habitacion) and ra.politica = 1 ORDER BY nombre ASC
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getAllRegisterSinHabitacionSelect($id_user){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ra.id_registro_acceso, CONCAT(ra.nombre, ' ', ra.segundo_nombre, ' ', ra.apellido_paterno, ' ',ra.apellido_materno, ' - ',ra.email,'') as nombre
      FROM registros_acceso ra
      WHERE ra.id_registro_acceso NOT IN (SELECT id_registro_acceso FROM asigna_habitacion) and ra.politica = 1 and ra.id_registro_acceso != $id_user ORDER BY nombre ASC
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getAllRegisterConHabitacion(){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ra.nombre, ra.segundo_nombre, ra.apellido_paterno, ra.apellido_materno, ah.*, ch.nombre_categoria, ua.nombre as nombre_administrador
      FROM registros_acceso ra
      INNER JOIN asigna_habitacion ah ON (ra.id_registro_acceso = ah.id_registro_acceso)
      INNER JOIN categorias_habitaciones ch ON (ch.id_categoria_habitacion = ah.id_categoria_habitacion)
      INNER JOIN utilerias_administradores ua ON(ua.utilerias_administradores_id = ah.utilerias_administradores_id)
      WHERE ra.politica = 1 
      GROUP BY ah.clave
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getAllRegisterConHabitacionByCategoria($id_habitacion){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ra.nombre,ah.id_asigna_habitacion, ch.id_categoria_habitacion, ua.nombre as nombre_administrador
      FROM registros_acceso ra
      INNER JOIN asigna_habitacion ah ON (ra.id_registro_acceso = ah.id_registro_acceso)
      INNER JOIN categorias_habitaciones ch ON (ch.id_categoria_habitacion = ah.id_categoria_habitacion)
      INNER JOIN utilerias_administradores ua ON(ua.utilerias_administradores_id = ah.utilerias_administradores_id)
      WHERE ra.politica = 1 and ch.id_categoria_habitacion = $id_habitacion
      GROUP BY ah.clave
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getUsuariosByClaveHabitacion($clave){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ah.id_registro_acceso, ah.clave, CONCAT(ra.nombre, ' ', ra.segundo_nombre, ' ', ra.apellido_paterno, ' ',ra.apellido_materno) as nombre, ra.email, ra.telefono, ra.img,ah.id_asigna_habitacion
      FROM asigna_habitacion ah
      INNER JOIN registros_acceso ra ON (ah.id_registro_acceso = ra.id_registro_acceso)
      WHERE ah.clave = '$clave'
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getCountAsistentesByClave($clave){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT count(*) as total_asignados
      FROM asigna_habitacion ah
      INNER JOIN registros_acceso ra ON (ah.id_registro_acceso = ra.id_registro_acceso)
      WHERE ah.clave = '$clave'
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getUsuarioByName($nombre){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT * FROM registros_acceso WHERE nombre LIKE '$nombre%'
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getAllRegistrosAcceso(){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT * FROM registrados
sql;
      return $mysqli->queryAll($query);
        
    }

    public static function getById($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT utilerias_asistentes_id, id_registro_acceso, usuario, contrasena, politica, status FROM utilerias_asistentes WHERE utilerias_asistentes_id = $id;
sql;
        return $mysqli->queryAll($query);
    }

    public static function getByClaveRA($clave){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ra.*, ra.id_registrado, ra.email FROM registrados ra
      WHERE ra.id_registrado = '$clave'
sql;
      return $mysqli->queryAll($query);
  }

    public static function getRegistroAccesoById($id){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ra.*, ra.ticket_virtual AS clave_ticket, CONCAT(ra.ticket_virtual,'.png') AS qr  FROM registro_acceso ra
      WHERE ra.id_registrado = $id
sql;
      return $mysqli->queryAll($query);
  }

  public static function getRegistradoById($clave){
    $mysqli = Database::getInstance();
    $query=<<<sql
    SELECT * FROM registrados
    WHERE id_registrado = '$clave'
sql;
    return $mysqli->queryAll($query);
}

  public static function getRegistroAccesoHabitacionByClaveRA($clave){
    $mysqli = Database::getInstance();
    $query=<<<sql
    SELECT ra.*, ah.id_habitacion as numero_habitacion
    FROM registros_acceso ra
    INNER JOIN asigna_habitacion ah
    ON ra.id_registro_acceso = ah.id_registro_acceso
    WHERE ra.clave = '$clave'
sql;
  return $mysqli->queryAll($query);
}

    public static function getHabitacionByNumber($numero_habitacion){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT DISTINCT ra.nombre as nombre_usuario, ra.apellido_paterno, ra.apellido_materno, ua.status as status_user, hh.numero_habitacion, ch.nombre_categoria, uad.nombre as nombre_administrador
      FROM registros_acceso ra
      INNER JOIN utilerias_asistentes ua ON (ra.id_registro_acceso = ua.id_registro_acceso) 
      INNER JOIN habitaciones_hotel hh ON (ra.id_habitacion = hh.id_habitacion) 
      INNER JOIN categorias_habitaciones ch ON (ch.id_categoria_habitacion = hh.id_categoria_habitacion)
      INNER JOIN utilerias_administradores uad ON (hh.utilerias_administradores_id = uad.utilerias_administradores_id)
      WHERE ra.id_registro_acceso = ua.id_registro_acceso
      and ra.politica = 1 and ua.status = 1 and hh.numero_habitacion = $numero_habitacion
sql;
      return $mysqli->queryAll($query);
  }

    public static function getTotalById($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM utilerias_asistentes ua INNER JOIN registros_acceso ra ON ua.id_registro_acceso = ra.id_registro_acceso WHERE ua.utilerias_asistentes_id = $id;
sql;
        return $mysqli->queryAll($query);
    }

    public static function getTotalByClaveRA($clave){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT * FROM registrados ra
      WHERE ra.id_registrado = '$clave'
sql;
      return $mysqli->queryAll($query);
  }

    public static function getIdRegistroAcceso($id){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT * FROM utilerias_asistentes WHERE id_registro_acceso = $id;
sql;
      return $mysqli->queryAll($query);
  }
    
  public static function insert($data){
    $mysqli = Database::getInstance(1);
    $query=<<<sql
    INSERT INTO registrados(email, nombre, apellidop, apellidom, prefijo, nombreconstancia, telefono, id_pais, id_estado, modalidad, monto_congreso, referencia,id_categoria,motivo)
    VALUES(:usuario, :nombre, :apellidop,:apellidom, :title, :nombreconstancia, :telefono, :pais, :estado, :modalidad, :monto_congreso, :referencia,:categoria, :motivo);
sql;

        $parametros = array(
        ':usuario'=>$data->_usuario,
        ':nombre'=>$data->_nombre,
        ':apellidop'=>$data->_apellidop,
        ':apellidom'=>$data->_apellidom,
        ':title'=>$data->_title,
        ':telefono'=>$data->_telefono,
        ':pais'=>$data->_pais,
        ':estado'=>$data->_estado,
        ':nombreconstancia'=>$data->_nombreconstancia,
        ':modalidad'=>$data->_modalidad,
        ':monto_congreso'=>$data->_monto_congreso,
        ':referencia'=>$data->_referencia,
        ':categoria'=>$data->_categoria,
        ':motivo'=>$data->_motivo,
        );
        $id = $mysqli->insert($query,$parametros);
        return $id;
      
  }

  public static function getCategoria()
    {
        $mysqli = Database::getInstance();
        $query = <<<sql
      SELECT * FROM categorias WHERE id_categoria;
sql;

        return $mysqli->queryAll($query);
        //$mysqli -> set_charset("utf8");
    }

    public static function getIdUsuarios($id)
    {
        $mysqli = Database::getInstance();
        $query = <<<sql
      SELECT * FROM registrados
      WHERE id_registrado = '$id'
sql;

        return $mysqli->queryOne($query);
        //$mysqli -> set_charset("utf8");
    }

  public static function getCategoriaMas()
    {
        $mysqli = Database::getInstance();
        $query = <<<sql
      SELECT * FROM categorias WHERE id_categoria != 1;
sql;

        return $mysqli->queryAll($query);
        //$mysqli -> set_charset("utf8");
    }
    
    public static function getCostoCategoria($id_categoria){
      $mysqli = Database::getInstance();
      $query =<<<sql
      SELECT costo
      FROM categorias WHERE
      id_categoria = $id_categoria        
sql;
  
      return $mysqli->queryOne($query);
    }

    public static function insertTicket($clave){
      $mysqli = Database::getInstance(true);
      $qr_code = $clave.'.png';
      $query=<<<sql
      INSERT INTO ticket_virtual (`clave`, `qr`) VALUES('$clave', '$qr_code')
sql;

      return $mysqli->insert($query);
    }

    public static function getPais(){       
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT * FROM paises
  sql;
      return $mysqli->queryAll($query);
    }

    public static function getEstado($id){       
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT * FROM estados WHERE id_pais = '$id';
  sql;
      return $mysqli->queryAll($query);
    }

    public static function getStateByCountry($id_pais){
      $mysqli = Database::getInstance(true);
      $query =<<<sql
      SELECT * FROM estados where id_pais = '$id_pais'
  sql;
    
      return $mysqli->queryAll($query);
    }

    public static function getUserRegister($email){
      $mysqli = Database::getInstance(true);
      $query =<<<sql
      SELECT * FROM registrados WHERE email = '$email'
  sql;
  
      return $mysqli->queryAll($query);
  }

    public static function update($data){
      $mysqli = Database::getInstance(true);
      $query=<<<sql
      UPDATE registrados SET 
      nombre = :nombre, apellidop = :apellido_paterno, apellidom = :apellido_materno, 
      telefono = :telefono, email = :email, id_categoria = :id_categoria, monto_congreso = :monto_congreso,
      id_pais = :pais, id_estado = :estado,modalidad = :modalidad
      WHERE email = :email;
sql;
      $parametros = array(
        
        ':nombre'=>$data->_nombre,
        ':apellido_paterno'=>$data->_apellido_paterno,
        ':apellido_materno'=>$data->_apellido_materno,
        ':telefono'=>$data->_telefono,
        ':email'=>$data->_email,
        ':id_categoria'=>$data->_id_categoria,
        ':monto_congreso'=>$data->_monto_congreso,
        ':pais'=>$data->_pais,
        ':estado'=>$data->_estado,
        ':modalidad'=>$data->_modalidad
      );

      $accion = new \stdClass();
      $accion->_sql= $query;
      $accion->_parametros = $parametros;
      $accion->_id = $data->_administrador_id;
      // UtileriasLog::addAccion($accion);
      return $mysqli->update($query, $parametros);
  }

    public static function generateCodeOnTable($email,$id_tv){
      $mysqli = Database::getInstance(true);
      // UPDATE registros_acceso SET clave = '$code', id_ticket_virtual = $id_tv WHERE email = '$email'
      $query=<<<sql
      UPDATE registros_acceso SET id_ticket_virtual = $id_tv WHERE email = '$email'
sql;

      return $mysqli->update($query);
    }

    public static function updateClaveRA($id,$clave){
      $mysqli = Database::getInstance(true);
      $query=<<<sql
      UPDATE registrados SET clave = '$clave' WHERE id_registro_acceso = '$id'
sql;

      return $mysqli->update($query);
    }

    public static function updateTicketVirtualRA($id,$clave){
      $mysqli = Database::getInstance(true);
      $query=<<<sql
      UPDATE registros_acceso SET ticket_virtual = '$clave' WHERE id_registro_acceso = '$id'
sql;

      return $mysqli->update($query);
    }

    public static function getIdTicket($clave){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT * FROM ticket_virtual WHERE clave = '$clave'
sql;
      return $mysqli->queryAll($query);
    }

    public static function getRegistroByEmail($email){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT * FROM registros_acceso WHERE email = '$email'
sql;
      return $mysqli->queryAll($query);
    }

    public static function getClaveByEmail($email){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT ra.*, ra.ticket_virtual AS clave_ticket, CONCAT(ra.ticket_virtual,'.png') AS qr FROM registros_acceso ra
      WHERE email = '$email';
sql;
      return $mysqli->queryAll($query);
  }

    public static function delete($id){
        
    }

    public static function insertImpresionConstancia($user_id,$tipo_constancia,$id_producto){
      $mysqli = Database::getInstance(true);
      $query=<<<sql
      INSERT INTO  impresion_constancia (user_id, tipo_constancia, id_producto,fecha_descarga) VALUES('$user_id', '$tipo_constancia','$id_producto',NOW())
sql;

      return $mysqli->insert($query);
    }

    public static function getDatauser($search){
      $mysqli = Database::getInstance();
      $query =<<<sql
      SELECT *
      FROM registrados WHERE
      status = 1 AND    
      CONCAT_WS(' ',email,nombre,apellidop,apellidom,id_registrado, clave,clave_socio) 
      LIKE '%$search%'
sql;
      
      return $mysqli->queryAll($query);
    }

    public static function getPendienesPagoUser($user_id){
      $mysqli = Database::getInstance();
      $query=<<<sql
      SELECT pp.id_pendiente_pago,p.id_producto,p.nombre as nombre_producto,ua.nombre as nombre_user,ua.clave_socio
      FROM pendiente_pago pp
      INNER JOIN productos p ON (p.id_producto = pp.id_producto)
      INNER JOIN registrados ua ON(pp.id_registrado = ua.id_registrado)
      WHERE  ua.id_registrado = $user_id and pp.STATUS = 0
sql;
      return $mysqli->queryAll($query);
  } 

  public static function getProductosNotInPendientesPagoAsignaProducto($user_id){
    $mysqli = Database::getInstance();
    $query=<<<sql
    SELECT p.id_producto, p.nombre as nombre_producto, ua.clave_socio, ua.monto_congreso
    FROM productos p
    INNER JOIN registrados ua
    WHERE id_producto NOT IN (SELECT id_producto FROM pendiente_pago WHERE id_registrado = $user_id) AND ua.id_registrado = $user_id 
sql;
    return $mysqli->queryAll($query);
}


}