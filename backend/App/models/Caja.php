<?php
namespace App\models;
defined("APPPATH") OR die("Access denied");

use \Core\Database;
use \App\interfaces\Crud;
use \App\controllers\UtileriasLog;


class Caja{

    public static function getById($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id';
sql;
        return $mysqli->queryAll($query);
    }

    public static function getProductosPendientesPagoAll($id_registrado){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT pp.*,'' as cantidad ,p.nombre, p.es_curso,p.es_servicio,p.es_congreso,p.precio_publico,p.tipo_moneda,ua.monto_congreso as amout_due,ua.clave_socio
        FROM pendiente_pago pp
        INNER JOIN productos p ON (pp.id_producto = p.id_producto)
        INNER JOIN utilerias_administradores ua ON(pp.id_registrado = ua.id_registrado)
        WHERE pp.id_registrado = $id_registrado AND pp.status = 0
sql;
        return $mysqli->queryAll($query);
      }

    public static function getProductosPendientesPagoTicketSitio($id_registrado){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT pp.*,'' as cantidad ,p.nombre, p.es_curso,p.es_servicio,p.es_congreso,p.precio_publico,p.tipo_moneda,ua.monto_congreso as amout_due,ua.clave_socio
        FROM pendiente_pago pp
        INNER JOIN productos p ON (pp.id_producto = p.id_producto)
        INNER JOIN utilerias_administradores ua ON(pp.id_registrado = ua.id_registrado)
        WHERE pp.id_registrado = $id_registrado AND pp.status = 0 GROUP BY pp.id_producto
sql;
        return $mysqli->queryAll($query);
      }

      public static function updateStatusPendientePago($id,$metodo_pago){
        $mysqli = Database::getInstance();
        $query=<<<sql
        UPDATE pendiente_pago SET status = 1, tipo_pago = '$metodo_pago'  WHERE id_pendiente_pago = $id
sql;
        return $mysqli->update($query);
      }

      public static function insertAsignaProducto($data){

        $mysqli = Database::getInstance();
        $query = <<<sql
        INSERT INTO asigna_producto (id_registrado,id_producto,fecha_asignacion,status) VALUES(:id_registrado,:id_producto,NOW(),1)                        
sql;
  
        $parametros = array(
            ':id_registrado' => $data->_id_registrado,
            ':id_producto' => $data->_id_producto
        );
  
        $id = $mysqli->insert($query, $parametros);
  
        return $id;
          
      }

      public static function insertPendientePago($data){

        $mysqli = Database::getInstance();
        $query = <<<sql
        INSERT INTO pendiente_pago (id_producto,id_registrado,reference,clave,fecha,monto,tipo_pago,status,comprado_en) VALUES(:id_producto,:id_registrado,:reference,:clave,:fecha,:monto,:tipo_pago,1,2)                        
sql;
  
        $parametros = array(            
            ':id_producto' => $data->_id_producto,
            ':id_registrado' => $data->_id_registrado,
            ':reference' => $data->_reference,
            ':clave' => $data->_clave,
            ':fecha' => date('Y-m-d'),
            ':monto' => $data->_monto,
            ':tipo_pago' => $data->_tipo_pago
        );
  
        $id = $mysqli->insert($query, $parametros);
  
        return $id;
          
      }


      public static function insertTransaccion($data){

        $mysqli = Database::getInstance();
        $query = <<<sql
        INSERT INTO transaccion_compra (id_registrado,referencia_transaccion,productos,total_pesos,tipo_pago,fecha_transaccion,descripcion,utilerias_administradores_id) VALUES(:id_registrado,:referencia_transaccion,:productos,:total_pesos,:tipo_pago,NOW(),:descripcion,:utilerias_administradores_id)                        
sql;
  
        $parametros = array(
            ':id_registrado' => $data->_id_registrado,
            ':referencia_transaccion' => $data->_referencia_transaccion,
            ':productos' => $data->_productos,
        //     ':total_dolares' => $data->_total_dolares,
            ':total_pesos' => $data->_total_pesos,
            ':tipo_pago' => $data->_tipo_pago,
            ':descripcion' => $data->_descripcion,
            ':utilerias_administradores_id' => $data->_utilerias_administradores_id 
        );

        // var_dump($parametros);
  
        $id = $mysqli->insert($query, $parametros);
  
        return $id;
          
      }

      public static function deletePendientesProductosByUser($id_registrado,$id_producto){
        $mysqli = Database::getInstance(true);
        $query=<<<sql
        DELETE FROM pendiente_pago WHERE id_producto = $id_producto AND id_registrado = $id_registrado
sql;
          return $mysqli->delete($query);
      }

      public static function getAsignaProductoByIdProductAndUser($id_registrado,$id_producto){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asigna_producto WHERE id_registrado = $id_registrado and id_producto = $id_producto;
sql;
        return $mysqli->queryOne($query);
      }

      public static function getPendientePagoById($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM pendiente_pago WHERE id_pendiente_pago = $id;
sql;
        return $mysqli->queryOne($query);
      }

      public static function getCountProductos($id_registrado,$id_producto){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT count(*) as numero_productos FROM pendiente_pago WHERE id_registrado = $id_registrado and id_producto = $id_producto;
sql;
        return $mysqli->queryAll($query);
      }
      

      public static function getTipoCambio(){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM tipo_cambio WHERE id_tipo_cambio = 1
sql;
        return $mysqli->queryOne($query);
      }

      public static function getDataUser($id_registrado){
        $mysqli = Database::getInstance(true);
        $query=<<<sql
        SELECT * FROM registrados WHERE id_registrado = '$id_registrado'
sql;
        return $mysqli->queryOne($query);
      }

      public static function getLastTransaccionByUser($id_registrado){
        $mysqli = Database::getInstance(true);
        $query=<<<sql
        SELECT * FROM transaccion_compra WHERE id_registrado = $id_registrado ORDER BY id_transaccion_compra  DESC LIMIT 1
sql;
        return $mysqli->queryOne($query);
      }

      public static function getTransaccion($id_transaccion){
        $mysqli = Database::getInstance(true);
        $query=<<<sql
        SELECT * FROM transaccion_compra WHERE id_transaccion_compra = $id_transaccion
sql;
        return $mysqli->queryOne($query);
      }

    public static function getByIdDirectivos($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id' and es_plenaria_individual = 1 and url_directivos != '';
sql;
        return $mysqli->queryAll($query);
    }

    public static function getByIdStaff($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id' and es_plenaria_individual = 1 and url_staf != '';
sql;
        return $mysqli->queryAll($query);
    }

    public static function getByIdNeurociencias($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id' and es_plenaria_individual = 1 and url_staf != '';
sql;
        return $mysqli->queryAll($query);
    }

    public static function getNeurociencias(){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT id_registro_asistencia, id_asistencia, ras.fecha_alta AS fecha_alta_r_asistencias, ra.img AS imagen, clave,
        CONCAT(ra.nombre, ' ', ra.apellido_paterno, ' ',ra.apellido_materno) AS nombre_completo
        FROM registros_asistencia ras
        INNER JOIN asistencias a
        INNER JOIN utilerias_asistentes ua
        INNER JOIN registros_acceso ra
        INNER JOIN linea_principal lp
        ON a.id_asistencia = ras.id_asistencias
        and ras.utilerias_asistentes_id = ua.utilerias_asistentes_id
        and ra.id_registro_acceso = ua.id_registro_acceso
sql;
        return $mysqli->queryAll($query);
    }

    public static function getByIdKaesOsteo($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id' and es_plenaria_individual = 1 and url_kaes_osteo != '';
sql;
        return $mysqli->queryAll($query);
    }

    public static function getByIdCardio($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id' and es_plenaria_individual = 1 and url_cardio != '';
sql;
        return $mysqli->queryAll($query);
    }

    public static function getByIdUro($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id' and es_plenaria_individual = 1 and url_uro != '';
sql;
        return $mysqli->queryAll($query);
    }


    public static function getByIdGastro($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id' and es_plenaria_individual = 1 and url_gastro != '';
sql;
        return $mysqli->queryAll($query);
    }

    public static function getByIdGineco($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id' and es_plenaria_individual = 1 and url_gineco != '';
sql;
        return $mysqli->queryAll($query);
    }

    public static function getByIdMedicinaGeneral($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id' and es_plenaria_individual = 1 and url_medicina_general != '';
sql;
        return $mysqli->queryAll($query);
    }

    public static function getByIdOle($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id' and es_plenaria_individual = 1 and url_ole != '';
sql;
        return $mysqli->queryAll($query);
    }

    public static function getByIdAnalgesia($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM asistencias WHERE clave = '$id' and es_plenaria_individual = 1 and url_analgesia != '';
sql;
        return $mysqli->queryAll($query);
    }

    public static function getInfo($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, ra.ticket_virtual as clave_ticket
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua
        ON ua.id_registro_acceso = ra.id_registro_acceso
        WHERE ra.ticket_virtual = '$clave'
sql;

        return $mysqli->queryAll($query);
    }


    public static function getInfoDirectivos($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, le.nombre
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua ON ua.id_registro_acceso = ra.id_registro_acceso
        INNER JOIN ticket_virtual tv ON tv.id_ticket_virtual = ra.id_ticket_virtual
        INNER JOIN linea_principal lp ON lp.id_linea_principal = ra.id_linea_principal
        INNER JOIN linea_ejecutivo le ON le.id_linea_ejecutivo = lp.id_linea_ejecutivo
        WHERE tv.clave = '$clave' and le.nombre = 'DIRECTIVOS';
sql;

        return $mysqli->queryAll($query);
    }


    public static function getInfoSTAFF($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, le.nombre
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua ON ua.id_registro_acceso = ra.id_registro_acceso
        INNER JOIN ticket_virtual tv ON tv.id_ticket_virtual = ra.id_ticket_virtual
        INNER JOIN linea_principal lp ON lp.id_linea_principal = ra.id_linea_principal
        INNER JOIN linea_ejecutivo le ON le.id_linea_ejecutivo = lp.id_linea_ejecutivo
        WHERE tv.clave = '$clave' and le.nombre = 'STAFF';
sql;

        return $mysqli->queryAll($query);
    }


    public static function getInfoNEUROCIENCIAS($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, le.nombre
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua ON ua.id_registro_acceso = ra.id_registro_acceso
        INNER JOIN ticket_virtual tv ON tv.id_ticket_virtual = ra.id_ticket_virtual
        INNER JOIN linea_principal lp ON lp.id_linea_principal = ra.id_linea_principal
        INNER JOIN linea_ejecutivo le ON le.id_linea_ejecutivo = lp.id_linea_ejecutivo
        WHERE tv.clave = '$clave' and le.nombre = 'NEUROCIENCIAS';
sql;

        return $mysqli->queryAll($query);
    }


    public static function getInfoKAESOSTEO($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, le.nombre
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua ON ua.id_registro_acceso = ra.id_registro_acceso
        INNER JOIN ticket_virtual tv ON tv.id_ticket_virtual = ra.id_ticket_virtual
        INNER JOIN linea_principal lp ON lp.id_linea_principal = ra.id_linea_principal
        INNER JOIN linea_ejecutivo le ON le.id_linea_ejecutivo = lp.id_linea_ejecutivo
        WHERE tv.clave = '$clave' and le.nombre = 'KAES / OSTEO';
sql;

        return $mysqli->queryAll($query);
    }


    public static function getInfoCARDIO($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, le.nombre
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua ON ua.id_registro_acceso = ra.id_registro_acceso
        INNER JOIN ticket_virtual tv ON tv.id_ticket_virtual = ra.id_ticket_virtual
        INNER JOIN linea_principal lp ON lp.id_linea_principal = ra.id_linea_principal
        INNER JOIN linea_ejecutivo le ON le.id_linea_ejecutivo = lp.id_linea_ejecutivo
        WHERE tv.clave = '$clave' and le.nombre = 'CARDIO';
sql;

        return $mysqli->queryAll($query);
    }


    public static function getInfoURO($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, le.nombre
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua ON ua.id_registro_acceso = ra.id_registro_acceso
        INNER JOIN ticket_virtual tv ON tv.id_ticket_virtual = ra.id_ticket_virtual
        INNER JOIN linea_principal lp ON lp.id_linea_principal = ra.id_linea_principal
        INNER JOIN linea_ejecutivo le ON le.id_linea_ejecutivo = lp.id_linea_ejecutivo
        WHERE tv.clave = '$clave' and le.nombre = 'URO';
sql;

        return $mysqli->queryAll($query);
    }


    public static function getInfoGASTRO($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, le.nombre
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua ON ua.id_registro_acceso = ra.id_registro_acceso
        INNER JOIN ticket_virtual tv ON tv.id_ticket_virtual = ra.id_ticket_virtual
        INNER JOIN linea_principal lp ON lp.id_linea_principal = ra.id_linea_principal
        INNER JOIN linea_ejecutivo le ON le.id_linea_ejecutivo = lp.id_linea_ejecutivo
        WHERE tv.clave = '$clave' and le.nombre = 'GASTRO';
sql;

        return $mysqli->queryAll($query);
    }


    public static function getInfoGINECO($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, le.nombre
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua ON ua.id_registro_acceso = ra.id_registro_acceso
        INNER JOIN ticket_virtual tv ON tv.id_ticket_virtual = ra.id_ticket_virtual
        INNER JOIN linea_principal lp ON lp.id_linea_principal = ra.id_linea_principal
        INNER JOIN linea_ejecutivo le ON le.id_linea_ejecutivo = lp.id_linea_ejecutivo
        WHERE tv.clave = '$clave' and le.nombre = 'GINECO';
sql;

        return $mysqli->queryAll($query);
    }


    public static function getInfoMEDICINAGENERAL($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, le.nombre
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua ON ua.id_registro_acceso = ra.id_registro_acceso
        INNER JOIN ticket_virtual tv ON tv.id_ticket_virtual = ra.id_ticket_virtual
        INNER JOIN linea_principal lp ON lp.id_linea_principal = ra.id_linea_principal
        INNER JOIN linea_ejecutivo le ON le.id_linea_ejecutivo = lp.id_linea_ejecutivo
        WHERE tv.clave = '$clave' and le.nombre = 'MEDICINA GENERAL';
sql;

        return $mysqli->queryAll($query);
    }


    public static function getInfoOLE($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, le.nombre
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua ON ua.id_registro_acceso = ra.id_registro_acceso
        INNER JOIN ticket_virtual tv ON tv.id_ticket_virtual = ra.id_ticket_virtual
        INNER JOIN linea_principal lp ON lp.id_linea_principal = ra.id_linea_principal
        INNER JOIN linea_ejecutivo le ON le.id_linea_ejecutivo = lp.id_linea_ejecutivo
        WHERE tv.clave = '$clave' and le.nombre = 'OLE';
sql;

        return $mysqli->queryAll($query);
    }


    public static function getInfoANALGESIA($clave){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT ra.*, ua.utilerias_asistentes_id, le.nombre
        FROM registros_acceso ra
        INNER JOIN utilerias_asistentes ua ON ua.id_registro_acceso = ra.id_registro_acceso
        INNER JOIN ticket_virtual tv ON tv.id_ticket_virtual = ra.id_ticket_virtual
        INNER JOIN linea_principal lp ON lp.id_linea_principal = ra.id_linea_principal
        INNER JOIN linea_ejecutivo le ON le.id_linea_ejecutivo = lp.id_linea_ejecutivo
        WHERE tv.clave = '$clave' and le.nombre = 'ANALGESIA';
sql;

        return $mysqli->queryAll($query);
    }

    public static function getEspecialidades(){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT *
        FROM linea_principal
sql;
        return $mysqli->queryAll($query);
    }

    public static function getBu(){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT *
        FROM bu
sql;
        return $mysqli->queryAll($query);
    }

    public static function getPosiciones(){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT *
        FROM posiciones
sql;
        return $mysqli->queryAll($query);
    }

    public static function getRegistrosAsistenciasByCode($code){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT a.nombre AS nombre_asistencia, ras.utilerias_asistentes_id, ua.usuario, ras.id_registro_asistencia, ras.status,
        ra.telefono, ra.email, ra.especialidad, lp.nombre AS nombre_especialidad,
        CONCAT (ra.nombre,' ',apellido_paterno,' ',apellido_materno) AS nombre_completo
        FROM registros_asistencia ras
        INNER JOIN asistencias a
        INNER JOIN utilerias_asistentes ua
        INNER JOIN registros_acceso ra
        INNER JOIN linea_principal lp
        ON a.id_asistencia = id_asistencias
        and ua.utilerias_asistentes_id = ras.utilerias_asistentes_id
        and ra.id_registro_acceso = ua.id_registro_acceso
        and lp.id_linea_principal = ra.especialidad
        
        WHERE a.clave = '$code'
sql;
        return $mysqli->queryAll($query);
    }

    public static function countLista($code){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT COUNT(*) FROM `registros_asistencia` ras
        INNER JOIN asistencias a
        ON ras.id_asistencias = a.id_asistencia
        WHERE a.clave = '$code'
sql;
        return $mysqli->queryAll($query);
    }

    public static function addRegister($id_asistencia,$id_user,$status){
        $mysqli = Database::getInstance();
        $query=<<<sql
        INSERT INTO registros_asistencia ( `id_asistencias`, `utilerias_asistentes_id`, `fecha_alta`, `status`) 
        VALUES ($id_asistencia,$id_user,NOW(),$status)
sql;
        $id = $mysqli->insert($query);
        $accion = new \stdClass();
        $accion->_sql= $query;
        // $accion->_parametros = $parametros;
        $accion->_id = $id;
        return $id_user;
    }

    public static function getIdRegistrosAsistenciasByCode($code){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT *
        FROM asistencias
        WHERE clave = '$code'
sql;
        return $mysqli->queryAll($query);
    }

    public static function findAsistantById($id,$id_asist){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM `registros_asistencia` 
        WHERE utilerias_asistentes_id = $id and id_asistencias = $id_asist
sql;
        return $mysqli->queryAll($query);
    }

    public static function delete($id_registro_asistencia){
        $mysqli = Database::getInstance(true);
        $query=<<<sql
        DELETE FROM `registros_asistencia` WHERE id_registro_asistencia = $id_registro_asistencia 
sql;
        return $mysqli->delete($query);

    }

//     public static function addRegister($asistencia){
//         $mysqli = Database::getInstance();
//         $query=<<<sql
//         INSERT INTO `registros_asistencia` (`id_asistencias`, `utilerias_asistentes_id`, `fecha_alta`, `status`) 
//         VALUES (1,'[value-2]','[value-3]','[value-4]','[value-5]')
// sql;
//         return $mysqli->queryAll($query);
//     }

/////////////////////CAJA////////////////////////
public static function getProductosPendComprados($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT pp.id_producto,pp.clave, pp.comprado_en,pp.status,ua.nombre,ua.clave_socio,aspro.status as estatus_compra,ua.monto_congreso as amout_due,pro.nombre as nombre_producto, pro.precio_publico, pro.tipo_moneda, pro.max_compra, pro.es_congreso, pro.es_servicio, pro.es_curso
        FROM pendiente_pago pp
        INNER JOIN registrados ua ON(ua.id_registrado = pp.id_registrado)
        INNER JOIN productos pro ON (pp.id_producto = pro.id_producto)
        LEFT JOIN asigna_producto aspro ON(pp.id_registrado = aspro.id_registrado AND pp.id_producto = aspro.id_producto)
        WHERE ua.id_registrado = $id GROUP BY id_producto;
sql;
        return $mysqli->queryAll($query);
      }

      public static function getProductosNoComprados($id){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT p.id_producto, p.nombre as nombre_producto, p.precio_publico, p.tipo_moneda, p.max_compra, p.es_congreso, p.es_servicio, p.es_curso, ua.clave_socio, ua.monto_congreso as amout_due 
        FROM productos p
        INNER JOIN registrados ua
        WHERE id_producto NOT IN (SELECT id_producto FROM pendiente_pago WHERE id_registrado = $id) AND ua.id_registrado = $id;
sql;
        return $mysqli->queryAll($query);
      }

      public static function getUserByPassword($usuario){
        $mysqli = Database::getInstance(true);
        $query =<<<sql
        SELECT * FROM utilerias_administradores WHERE contrasena LIKE :password 
sql;
        $params = array(
            ':password'=>$usuario->_password
        );

        return $mysqli->queryOne($query,$params);
      }

      public static function pendientesPagoByProductAndUser($id_registrado,$id_producto){
        $mysqli = Database::getInstance();
        $query=<<<sql
        SELECT * FROM pendiente_pago WHERE id_registrado = $id_registrado and id_producto = $id_producto;
sql;
        return $mysqli->queryOne($query);
      }

      public static function updateStatusPendientePagoByUserAndId($id_registrado,$id_producto,$metodo_pago,$monto){
        $mysqli = Database::getInstance();
        $query=<<<sql
        UPDATE pendiente_pago SET status = 1, tipo_pago = '$metodo_pago', monto = $monto, comprado_en = 2  WHERE id_registrado = $id_registrado and id_producto = $id_producto
sql;
        return $mysqli->update($query);
      }

      public static function UpdateFiscalData($usuario){
        $mysqli = Database::getInstance(true);
        $query =<<<sql
        UPDATE registrados SET 
        razon_social = :business_name_iva, rfc = :code_iva, email_fac = :email_receipt_iva, 
        direccion_fiscal = :direccion, cp_fiscal = :postal_code_iva WHERE id_registrado = :id_registrado
sql;
        $params = array(
            ':id_registrado'=>$usuario->_id_registrado,
            ':business_name_iva' => $usuario->_business_name_iva,
            ':code_iva' => $usuario->_code_iva,
            ':email_receipt_iva' => $usuario->_email_receipt_iva,
            ':direccion' => $usuario->_direccion,
            ':postal_code_iva' => $usuario->_postal_code_iva
        );
        return $mysqli->update($query,$params);
      }
}