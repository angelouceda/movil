<?php

namespace App\Http\Controllers;


use App\Models\Report_enter;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class ReportEnterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $fecha_inicio =  "'" . date('Y-m-d') . "'";
        $fecha_fin = "'" . date('Y-m-d') . "'";

        $marcas = $this->getMarcas();
        $reporte = $this->getReporteMarcaVenta($fecha_inicio, $fecha_fin,  $this->getIDMarcas($marcas));
        $datos = $this->getDatosVentas($fecha_inicio, $fecha_fin);

        return view('reporte_venta', compact('datos', 'marcas', 'reporte'));
    }

    public function submitForm(Request $request)
    {

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $fecha_inicio = $request->input('fecha_inicio');
            $fecha_fin = $request->input('fecha_fin');

            $marcas_seleccionadas = array();
            $marcas_seleccionadas = $request->input('marca',[]);
        }
        $marcas = $this->getMarcas();

        if ($request->filled('select_radio') && $request->input('select_radio') == 1) {
            //var_dump($marcas_seleccionadas);
            $reporte = $this->getReporteMarcaVenta($fecha_inicio, $fecha_fin, $marcas_seleccionadas);
        } else {
            $reporte = $this->getReporteVentaTienda($fecha_inicio, $fecha_fin, $this->getIDMarcas($marcas));
            //$metas = $this->getMetastienda($reporte);
        }
        
        $datos = $this->getDatosVentas($fecha_inicio, $fecha_fin);
        
        return view('reporte_venta', compact('datos', 'marcas', 'reporte'));
    }

    public function getDatosVentas($fecha_inicio, $fecha_fin)
    {
        $datos = DB::table('datamart_ventas_actual')
            ->select('sucursal_marca', DB::raw('SUM(cantidad) as cantidad'), DB::raw('SUM(importe_subtotal) as importe_subtotal'), DB::raw('SUM(importe_impuesto) as importe_impuesto'), DB::raw('SUM(importe_total) as importe_total'))
            ->whereBetween('fecha_creacion', [$fecha_inicio, $fecha_fin])
            ->groupBy('sucursal_marca')
            ->get();

        return $datos;
    }
    /*Esta es la funcion getReporteMarcaVenta, está bien?*/
    public function getReporteMarcaVenta($fecha_inicio, $fecha_fin, $marcas_select)
    {
        $bi_conexion = DB::connection('pgsql2');
        // Quiero extraer los valores de la columna nombres 
        $resultados = $bi_conexion->table('reporting_datamartventasclon')
            ->select('reporting_marcas.marca as nombre', 'reporting_datamartventasclon.sucursal_marca_id', DB::raw('SUM(reporting_datamartventasclon.importe_subtotal) AS importe_total_sum'))
            ->join('reporting_marcas', 'reporting_datamartventasclon.sucursal_marca_id', '=', 'reporting_marcas.id')
            ->whereNotNull('reporting_marcas.idmarca')
            ->whereBetween('reporting_datamartventasclon.fecha_documento', [$fecha_inicio, $fecha_fin])
            ->whereIn('reporting_datamartventasclon.sucursal_marca_id', $marcas_select)
            ->groupBy('reporting_marcas.marca', 'reporting_datamartventasclon.sucursal_marca_id')
            ->orderByDesc('importe_total_sum')
            ->get();

        return $resultados;
    }

    public function getReporteVentaTienda($fecha_inicio, $fecha_fin, $tiendas_select)
    {
        $bi_conexion = DB::connection('pgsql2');
        $resultados = $bi_conexion->table('reporting_datamartventasclon')
            ->select('reporting_datamartventasclon.sucursal as nombre','reporting_datamartventasclon.sucursal_marca_id',DB::raw('SUM(reporting_datamartventasclon.importe_subtotal) AS importe_total_sum'))
            ->join('reporting_marcas', 'reporting_datamartventasclon.sucursal_marca_id', '=', 'reporting_marcas.id')
            ->whereNotNull('reporting_marcas.idmarca')
            ->whereBetween('reporting_datamartventasclon.fecha_documento', [$fecha_inicio, $fecha_fin])
            ->whereIn('reporting_datamartventasclon.sucursal_marca_id', $tiendas_select)
            ->groupBy('reporting_datamartventasclon.sucursal', 'reporting_datamartventasclon.sucursal_marca_id')
            ->orderBy('importe_total_sum', 'asc')
            ->get();

        return $resultados;
    }

    public function getMetastienda($reporte){

        $idsucursales = [];
        foreach($reporte as $value){
            $idsucursales = $value->nombre;
        }

        $bi_conexion = DB::connection('pgsql2');
        $resultados = $bi_conexion->table('reporting_datamartventascuotaclon')
        ->select(DB::raw('sum(importe) AS importe'), 'sucursal')
        ->whereIn('sucursal', $idsucursales)
        ->orderBy('importe', 'DESC')
        ->get();

        return $resultados;
    }

    public function getMarcas()
    {
        $bi_conexion = DB::connection('pgsql2');
        $marcas = $bi_conexion->table('reporting_marcas')->select('*')
            ->get();
        return $marcas;
    }

    public function getIDMarcas($marcas)
    {
        $marcas_select = [];
        foreach ($marcas as $value) {
            $marcas_select[] = $value->id;
        }
        return $marcas_select;
    }

    public function ValidarPermisos($id){
       
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Report_enter  $report_enter
     * @return \Illuminate\Http\Response
     */
    public function show(Report_enter $report_enter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Report_enter  $report_enter
     * @return \Illuminate\Http\Response
     */
    public function edit(Report_enter $report_enter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Report_enter  $report_enter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Report_enter $report_enter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Report_enter  $report_enter
     * @return \Illuminate\Http\Response
     */
    public function destroy(Report_enter $report_enter)
    {
        //
    }
}
