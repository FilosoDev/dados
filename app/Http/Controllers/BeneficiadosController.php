<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Charts\GenericChart;
use Uspdev\Cache\Cache;
use Maatwebsite\Excel\Excel;
use App\Exports\DadosExport;

class BeneficiadosController extends Controller
{
    private $data;
    private $excel;

    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
        $cache = new Cache();
        $data = [];
        $anos = ['2010', '2011', '2012', '2013','2014', '2015', '2016', '2017', '2018', '2019', '2020'];
        /* Contabiliza alunos com beneficios ativos de 2010-2020 */
        $query = file_get_contents(__DIR__ . '/../../../Queries/conta_beneficiados_ano.sql');
        foreach($anos as $ano){
            $query_por_ano = str_replace('__ano__', $ano, $query);
            $result = $cache->getCached('\Uspdev\Replicado\DB::fetch',$query_por_ano);
            $data[$ano] = $result['computed'];
        }

        $this->data = $data;
    }    
    
    public function grafico(){
        /* Tipos de gráficos:
         * https://www.highcharts.com/docs/chart-and-series-types/chart-types
         */
        $chart = new GenericChart;

        $chart->labels(array_keys($this->data));
        $chart->dataset('Quantidade de pessoas com benefícios', 'line', array_values($this->data));

        return view('beneficiados', compact('chart'));
    }

    public function export($format)
    {
        if($format == 'excel') {
            $export = new DadosExport([$this->data],array_keys($this->data));
            return $this->excel->download($export, 'ativos_beneficios.xlsx'); 
        }
    }
}
