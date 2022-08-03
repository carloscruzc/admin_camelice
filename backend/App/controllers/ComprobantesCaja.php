<?php

namespace App\controllers;

defined("APPPATH") or die("Access denied");
require_once dirname(__DIR__) . '/../public/librerias/mpdf/mpdf.php';
require_once dirname(__DIR__) . '/../public/librerias/fpdf/fpdf.php';
require_once dirname(__DIR__) . '/../public/librerias/phpqrcode/qrlib.php';

use \Core\View;
use \Core\MasterDom;
use \App\controllers\Contenedor;
use \Core\Controller;
use \App\models\PruebasCovidSitio as PruebasCovidSitioDao;
use \App\models\Asistencias as AsistenciasDao;
use \App\models\Conceptos as ConceptosDao;
use \App\models\ComprobantesCaja as ComprobantesCajaDao;
use \App\models\Caja as CajaDao;
use \DateTime;
use \DatetimeZone;
// use \App\models\Linea as LineaDao;

class ComprobantesCaja extends Controller
{

  private $_contenedor;

  function __construct()
  {
    parent::__construct();
    $this->_contenedor = new Contenedor;
    View::set('header', $this->_contenedor->header());
    View::set('footer', $this->_contenedor->footer());
    // if (Controller::getPermisosUsuario($this->__usuario, "seccion_asistencias", 1) == 0)
    //   header('Location: /Principal/');
  }

  public function getUsuario()
  {
    return $this->__usuario;
  }

  public function index()
  {
    $extraHeader = <<<html
html;


$extraFooter =<<<html
      <script>
        $(document).ready(function(){

          $('#asistencia-list').DataTable({
            "drawCallback": function(settings) {
                $('.current').addClass("btn bg-gradient-pink text-white btn-rounded").removeClass("paginate_button");
                $('.paginate_button').addClass("btn").removeClass("paginate_button");
                $('.dataTables_length').addClass("m-4");
                $('.dataTables_info').addClass("mx-4");
                $('.dataTables_filter').addClass("m-4");
                $('input').addClass("form-control");
                $('select').addClass("form-control");
                $('.previous.disabled').addClass("btn-outline-info opacity-5 btn-rounded mx-2");
                $('.next.disabled').addClass("btn-outline-info opacity-5 btn-rounded mx-2");
                $('.previous').addClass("btn-outline-info btn-rounded mx-2");
                $('.next').addClass("btn-outline-info btn-rounded mx-2");
                $('a.btn').addClass("btn-rounded");
            },
            "language": {

                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }

            }
        });

          $("#muestra-cupones").tablesorter();
          var oTable = $('#muestra-cupones').DataTable({
                "columnDefs": [{
                    "orderable": false,
                    "targets": 0
                }],
                 "order": false
            });

            // Remove accented character from search input as well
            $('#muestra-cupones input[type=search]').keyup( function () {
                var table = $('#example').DataTable();
                table.search(
                    jQuery.fn.DataTable.ext.type.search.html(this.value)
                ).draw();
            });

            var checkAll = 0;
            $("#checkAll").click(function () {
              if(checkAll==0){
                $("input:checkbox").prop('checked', true);
                checkAll = 1;
              }else{
                $("input:checkbox").prop('checked', false);
                checkAll = 0;
              }

            });

            $("#export_pdf").click(function(){
              $('#all').attr('action', '/Empresa/generarPDF/');
              $('#all').attr('target', '_blank');
              $("#all").submit();
            });

            $("#export_excel").click(function(){
              $('#all').attr('action', '/Empresa/generarExcel/');
              $('#all').attr('target', '_blank');
              $("#all").submit();
            });

            $("#delete").click(function(){
              var seleccionados = $("input[name='borrar[]']:checked").length;
              if(seleccionados>0){
                alertify.confirm('¿Segúro que desea eliminar lo seleccionado?', function(response){
                  if(response){
                    $('#all').attr('target', '');
                    $('#all').attr('action', '/Empresa/delete');
                    $("#all").submit();
                    alertify.success("Se ha eliminado correctamente");
                  }
                });
              }else{
                alertify.confirm('Selecciona al menos uno para eliminar');
              }
            });

        });
      </script>
html;
    $tabla = '';
    $datos = ComprobantesCajaDao::getAll();
    
    
    foreach ($datos as $key => $value) {

      $tabla.=<<<html
      <tr>
        <td>{$value['id_transaccion_compra']}</td>
        <td>{$value['nombre_user']}</td>
        <td id="descripcion_asistencia" width="20">{$value['productos']}</td>
        <td class="text-center">{$value['total_pesos']}</td>        
        <td class="text-center">{$value['fecha_transaccion']}</td> 
        <td class="text-center">{$value['nombre_caja']}</td>
        <td class="text-center">
        <a href='/ComprobantesCaja/print/{$value['id_transaccion_compra']}' style='' class='btn btn-icon-only btn-info' value={$value['id_registrado']} data-bs-toggle="tooltip" target="_blank" data-bs-placement="left" data-bs-original-title="ver comprobante"><i class="fa fal fa-file"></i></a>
        </td>
      </tr>
 
html;
    }

    $num_asistencias = AsistenciasDao::getNumAsistencias()['total'];
    $date = date("Y").'-'.date("m").'-'.date("d");

      $productos = '';
      foreach (AsistenciasDao::getProductos() as $key => $value) {
          $productos .=<<<html
      <option value="{$value['id_producto']}"> {$value['nombre']}</option>
html;
      }


      // View::set('lineas',$lineas);
      View::set('tabla',$tabla);
      View::set('num_asistencias',$num_asistencias);
      View::set('asideMenu',$this->_contenedor->asideMenu());
      View::set('header',$this->_contenedor->header($extraHeader));
      View::set('footer',$this->_contenedor->footer($extraFooter));
      View::set('productos',$productos);
      View::render("comprobante_caja_all");
    }

    public function comprobantes()
  {
    $extraHeader = <<<html
html;


$extraFooter =<<<html
      <script>
        $(document).ready(function(){

          $('#asistencia-list').DataTable({
            "drawCallback": function(settings) {
                $('.current').addClass("btn bg-gradient-pink text-white btn-rounded").removeClass("paginate_button");
                $('.paginate_button').addClass("btn").removeClass("paginate_button");
                $('.dataTables_length').addClass("m-4");
                $('.dataTables_info').addClass("mx-4");
                $('.dataTables_filter').addClass("m-4");
                $('input').addClass("form-control");
                $('select').addClass("form-control");
                $('.previous.disabled').addClass("btn-outline-info opacity-5 btn-rounded mx-2");
                $('.next.disabled').addClass("btn-outline-info opacity-5 btn-rounded mx-2");
                $('.previous').addClass("btn-outline-info btn-rounded mx-2");
                $('.next').addClass("btn-outline-info btn-rounded mx-2");
                $('a.btn').addClass("btn-rounded");
            },
            "language": {

                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }

            }
        });

          $("#muestra-cupones").tablesorter();
          var oTable = $('#muestra-cupones').DataTable({
                "columnDefs": [{
                    "orderable": false,
                    "targets": 0
                }],
                 "order": false
            });

            // Remove accented character from search input as well
            $('#muestra-cupones input[type=search]').keyup( function () {
                var table = $('#example').DataTable();
                table.search(
                    jQuery.fn.DataTable.ext.type.search.html(this.value)
                ).draw();
            });

            var checkAll = 0;
            $("#checkAll").click(function () {
              if(checkAll==0){
                $("input:checkbox").prop('checked', true);
                checkAll = 1;
              }else{
                $("input:checkbox").prop('checked', false);
                checkAll = 0;
              }

            });

            $("#export_pdf").click(function(){
              $('#all').attr('action', '/Empresa/generarPDF/');
              $('#all').attr('target', '_blank');
              $("#all").submit();
            });

            $("#export_excel").click(function(){
              $('#all').attr('action', '/Empresa/generarExcel/');
              $('#all').attr('target', '_blank');
              $("#all").submit();
            });

            $("#delete").click(function(){
              var seleccionados = $("input[name='borrar[]']:checked").length;
              if(seleccionados>0){
                alertify.confirm('¿Segúro que desea eliminar lo seleccionado?', function(response){
                  if(response){
                    $('#all').attr('target', '');
                    $('#all').attr('action', '/Empresa/delete');
                    $("#all").submit();
                    alertify.success("Se ha eliminado correctamente");
                  }
                });
              }else{
                alertify.confirm('Selecciona al menos uno para eliminar');
              }
            });

        });
      </script>
html;
    $tabla = '';
    $datos = ComprobantesCajaDao::getAll();
    
    
    foreach ($datos as $key => $value) {

      $tabla.=<<<html
      <tr>
        <td>{$value['id_transaccion_compra']}</td>
        <td>{$value['nombre_user']}</td>
        <td id="descripcion_asistencia" width="20">{$value['productos']}</td>
        <td class="text-center">{$value['total_pesos']}</td>        
        <td class="text-center">{$value['fecha_transaccion']}</td> 
        <td class="text-center">{$value['nombre_caja']}</td>
        <td class="text-center">
        <a href='/ComprobantesCaja/print/{$value['id_transaccion_compra']}' style='' class='btn btn-icon-only btn-info' value={$value['id_registrado']} data-bs-toggle="tooltip" target="_blank" data-bs-placement="left" data-bs-original-title="ver comprobante"><i class="fa fal fa-file"></i></a>
        </td>
      </tr>
 
html;
    }

    $num_asistencias = AsistenciasDao::getNumAsistencias()['total'];
    $date = date("Y").'-'.date("m").'-'.date("d");

      $productos = '';
      foreach (AsistenciasDao::getProductos() as $key => $value) {
          $productos .=<<<html
      <option value="{$value['id_producto']}"> {$value['nombre']}</option>
html;
      }


      // View::set('lineas',$lineas);
      View::set('tabla',$tabla);
      View::set('num_asistencias',$num_asistencias);
      // View::set('asideMenu',$this->_contenedor->asideMenu());
      View::set('header',$this->_contenedor->header($extraHeader));
      View::set('footer',$this->_contenedor->footer($extraFooter));
      View::set('productos',$productos);
      View::render("comprobante_caja_all");
    }

    public function print($id_transaccion)
    {
        date_default_timezone_set('America/Mexico_City');

        // $this->generaterQr($clave);  
        
        $productos = CajaDao::getTransaccion($id_transaccion);

        $datos_user = CajaDao::getDataUser($productos['id_registrado']);
        $user_id = $datos_user['id_registrado'];

        
        $reference = $productos['referencia_transaccion'];
        $fecha = $productos['fecha_transaccion'];
        $tipo_pago = $productos['tipo_pago'];
        $id_transaccion = $productos['id_transaccion_compra'];


        if(strlen($id_transaccion) == 1){
            $ini_folio = '000';
        }elseif(strlen($id_transaccion) == 2){
            $ini_folio = '00';
        }elseif(strlen($id_transaccion) == 3){
            $ini_folio = '0';
        }else{
            $ini_folio = '';
        }
        
        $nombre_completo = $datos_user['nombre'] . " " . $datos_user['apellidop'] . "\n " . $datos_user['apellidom'];


        $pdf = new \FPDF($orientation = 'P', $unit = 'mm', $format = 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 8);    //Letra Arial, negrita (Bold), tam. 20
        $pdf->setY(1);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Image('plantillas/orden.png', 0, 0, 210, 300);
        
        // $pdf->SetFont('Arial', 'B', 25);
        // $pdf->Multicell(133, 80, $clave_ticket, 0, 'C');

        //$pdf->Image('1.png', 1, 0, 190, 190);
        $pdf->SetFont('Arial', 'B', 5);    //Letra Arial, negrita (Bold), tam. 20
        //$nombre = utf8_decode("Jonathan Valdez Martinez");
        //$num_linea =utf8_decode("Línea: 39");
        //$num_linea2 =utf8_decode("Línea: 39");

        $espace = 105;
        $total = array();
        $pro = explode(",",$productos['productos']);

 
        foreach($pro as $key => $value){  

            // $total_productos = CajaDao::getCountProductos($user_id,2)[0];

            // $count_productos = $total_productos['numero_productos'];

            $pro_precio = explode("-",$value);
            $cantidad = $pro_precio[0];
            $solo_precio = explode("$",$pro_precio[2]); //precio unitario producto
            $cantidad = explode(".",$pro_precio[0]);
            $solo_cantidad = $cantidad[1]; //cantidad de compra
 
            // echo number_format($solo_precio[1],2);

            //Nombre Curso
            $pdf->SetXY(22, $espace);
            $pdf->SetFont('Arial', 'B', 8);  
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Multicell(100, 4, utf8_decode($pro_precio[1]) , 0, 'C');

            //Costo
            $pdf->SetXY(115, $espace);
            $pdf->SetFont('Arial', 'B', 8);  
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Multicell(100, 4, number_format($solo_precio[1],2) ." MXN", 0, 'C');

            //Cantidad
            $pdf->SetXY(18, $espace);
            $pdf->SetFont('Arial', 'B', 8);  
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Multicell(20, 4, $solo_cantidad , 0, 'C');

            //Total
            $pdf->SetXY(138, $espace);
            $pdf->SetFont('Arial', 'B', 8);  
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Multicell(100, 4, number_format(($solo_precio[1]*$solo_cantidad),2)." MXN" , 0, 'C');

            $espace = $espace + 6;
        }

        $tipo_cambio = CajaDao::getTipoCambio()['tipo_cambio'];
        

        //folio
        $pdf->SetXY(5, 50);
        $pdf->SetFont('Arial', 'B', 13);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, $ini_folio.$id_transaccion, 0, 'C');

        //fecha
        $pdf->SetXY(120,65);
        $pdf->SetFont('Arial', 'B', 13);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, $fecha, 0, 'C');

        //Nombre
        $pdf->SetXY(120,20);
        $pdf->SetFont('Arial', 'B', 10);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, utf8_decode($nombre_completo), 0, 'C');

        //Nombre empresa
        $pdf->SetXY(120,35);
        $pdf->SetFont('Arial', 'B', 10);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, utf8_decode($datos_user['business_name_iva']), 0, 'C');

        //RFC
        $pdf->SetXY(120,40);
        $pdf->SetFont('Arial', 'B', 10);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, utf8_decode($datos_user['code_iva']), 0, 'C');

        //RFC
        $pdf->SetXY(120,45);
        $pdf->SetFont('Arial', 'B', 10);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, utf8_decode($datos_user['email_receipt_iva']), 0, 'C');

        

      

        //total dolares
        // $pdf->SetXY(125, 199);
        // $pdf->SetFont('Arial', 'B', 13);  
        // $pdf->SetTextColor(0, 0, 0);
        // $pdf->Multicell(100, 10, number_format($productos['total_dolares']).' USD', 0, 'C');

        //total pesos
        $pdf->SetXY(138, 265);
        $pdf->SetFont('Arial', 'B', 13);  
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Multicell(100, 10, '$ '.number_format($productos['total_pesos'],2).'', 0, 'C');

        //tipo pago
        // $pdf->SetXY(125, 265);
        // $pdf->SetFont('Arial', 'B', 13);  
        // $pdf->SetTextColor(0, 0, 0);
        // $pdf->Multicell(100, 10, $tipo_pago, 0, 'C');

        //imagen Qr
        // $pdf->Image('qrs/'.$clave.'.png' , 152 ,245, 35 , 38,'PNG');


        $pdf->Output();
        // $pdf->Output('F','constancias/'.$clave.$id_curso.'.pdf');

        // $pdf->Output('F', 'C:/pases_abordar/'. $clave.'.pdf');
    }
  

    public function conceptosAdd() {

      $nombre = $_POST['nombre'];
      $descripcion = $_POST['descripcion'];
      $tipo = $_POST['tipo'];
      $precio = $_POST['precio_publico'];

      $data = new \stdClass();
      $data->_clave = $this->generateRandomString();
      $data->_nombre = $nombre;
      $data->_descripcion = $descripcion;
      $data->_tipo = $tipo;
      $data->_precio = $precio;      
  
      $id = ConceptosDao::insert($data);
      if($id >= 1){
        // $this->alerta($id,'add');
        echo '<script>
          alert("Concepto Registrada con exito");
          window.location.href = "/Conceptos";
        </script>';

       
      }else{
        // $this->alerta($id,'error');
        echo '<script>
        alert("Error al registrar el concepto, consulte a soporte");
        window.location.href = "/Conceptos";
      </script>';
      }


    }


    public function deleteProduct(){
      $id_producto = $_POST['id_producto'];

      $delete = ConceptosDao::deleteProducto($id_producto);

      if($delete){
        echo "success";
      }else{
        echo "fail";
      }
    }



    function generateRandomString($length = 6) { 
      return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length); 
  } 

  // View::set('permisoGlobalHidden', $permisoGlobalHidden);
  // View::set('asistentesHidden', $asistentesHidden);
  // View::set('vuelosHidden', $vuelosHidden);
  // View::set('pickUpHidden', $pickUpHidden);
  // View::set('habitacionesHidden', $habitacionesHidden);
  // View::set('cenasHidden', $cenasHidden);
  // View::set('aistenciasHidden', $aistenciasHidden);
  // View::set('vacunacionHidden', $vacunacionHidden);
  // View::set('pruebasHidden', $pruebasHidden);
  // View::set('configuracionHidden', $configuracionHidden);
  // View::set('utileriasHidden', $utileriasHidden);
  // View::set('header', $this->_contenedor->header($extraHeader));
  // View::set('footer', $this->_contenedor->footer($extraFooter));
  // View::render("asistencias_all");

}