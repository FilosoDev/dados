<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Charts\GenericChart;
use Uspdev\Cache\Cache;
use Maatwebsite\Excel\Excel;
use App\Exports\DadosExport;

class AtivosPorDepartamentoFuncionariosController extends Controller
{
    private $data;
    private $excel;

    public function __construct(Excel $excel){
        $this->excel = $excel;
        $cache = new Cache();
        $data = [];

        $departamentos = ['FLA','FLP','FLF','FLH','FLC','FLM','FLO','FLL','FSL', 'FLT','FLG',];

        $query = file_get_contents(__DIR__ . '/../../../Queries/conta_funcionarios_departamento.sql');
        
        /* Contabiliza funcionários ativos por departamento */
        foreach ($departamentos as $departamento){
            $query_por_departamento = str_replace('__departamento__', $departamento, $query);
            $result = $cache->getCached('\Uspdev\Replicado\DB::fetch',$query_por_departamento);
            $data[$departamento] = $result['computed'];
        }
        $this->data = $data;
    }    
    
    public function grafico(){
        /* Tipos de gráficos:
         * https://www.highcharts.com/docs/chart-and-series-types/chart-types
         */
        $chart = new GenericChart;

        $chart->labels(array_keys($this->data));
        $chart->dataset('Quantidade', 'pie', array_values($this->data));

        return view('ativosFuncionariosDepartamento', compact('chart'));
    }

    public function export($format){
        if($format == 'excel') {
            $export = new DadosExport([$this->data],array_keys($this->data));
            return $this->excel->download($export, 'ativos_por_departamento_funcionarios.xlsx');
        }
    }

}
