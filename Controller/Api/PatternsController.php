<?php
namespace api\Controller;

include(PROJECT_ROOT_PATH."Utils/CsvUtils.class.php");
include(PROJECT_ROOT_PATH."Utils/stock_chart_pattern.class.php");
include(PROJECT_ROOT_PATH."Utils/Chartdata.class.php");
include(PROJECT_ROOT_PATH."Utils/Statistics.php");
include(PROJECT_ROOT_PATH."Utils/pattern_encrypt_decrypt.php");
use Patterns\StockChartPatterns;
use Patterns\Chart;
use Utils\CsvUtils;
use Patterns\Statistics;
use Patterns\PatternCryptModel;

class PatternsController extends BaseController
{
    public function modelAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        if (strtoupper($requestMethod) != 'GET' 
            || !isset($arrQueryStringParams['s']) 
                || trim($arrQueryStringParams['s']) == "") { 
            $strErrorDesc = 'Invalid Request ... Nothing to serve ....';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
            array('Content-Type: application/json', $strErrorHeader)
            );
        }
        $col_no = (!isset($arrQueryStringParams['col_no']))?"0":trim($arrQueryStringParams["col_no"],"\"'");//$_GET['col_no'];
        $this->startIndex = (!isset($arrQueryStringParams['strt_indx']))?"1":trim($arrQueryStringParams["strt_indx"],"\"'");
        $this->len = (!isset($arrQueryStringParams['l']))?"5":trim($arrQueryStringParams["l"],"\"'");
        $reverse_read = (isset($arrQueryStringParams['reverse_read'])? true:false);
        $range = (isset($arrQueryStringParams['min_range'])? trim($arrQueryStringParams['min_range'],"\"'"):0);
        // this is filter that will break the grid on smaller subgrids , default , would be 2 , which make 
        // subgrid of 2 x 2 = 4 cells , if user add 3 mean subgrid of 3 x 3 = 9 cells and so on ,
        // this will be subgrid that will filter big grid define by ($this->len ** 2) = grid cells
        // the retrun will be reduce order of array that contains pattern sequence
        $filter = (isset($arrQueryStringParams['filter'])? trim($arrQueryStringParams['filter'],"\"'"):2);
        //$this->gridRows = (!isset($arrQueryStringParams['min_efficiency']))?"5":trim($arrQueryStringParams["min_efficiency"],"\"'");
        //$accuracy = (!isset($_GET['min_accuracy']))?"0.5":trim($_GET["min_accuracy"],"\"'");
        $header = (isset($arrQueryStringParams['header'])? true : false);
        $this->$scv = new CsvUtils();
        $this->chart = new Chart();
        $url = trim($arrQueryStringParams["s"],"\"'");
        $this->data = $this->$scv->google_sheet_read_csv($url,-1,$header,$reverse_read);
        if ( $this->data &&  $this->data["err"])
        {
           $strErrorDesc =  $this->data;
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
            array('Content-Type: application/json', $strErrorHeader)
            );
        }
        $ma = (!isset($_GET['ma']))?21:trim($_GET["ma"],"\"'");
        $this->columnData = array_column($this->data,(int)$col_no);
        $ma_arr = [];
        $stat = new Statistics();
        $stat->moving_average($this->columnData,$ma,$ma_arr);

        $this->$charts = new StockChartPatterns($ma_arr);
        $grid = $this->$charts->constractModel($this->startIndex,$this->len,$filter,$range);
        //print_r($grid);
        
        //$grid = $this->$charts->createModelGrid($startIndx,$this->len,$this->gridRows);
        $ret["col_no"] = $col_no;
        $ret["strt_indx"] = $this->startIndex;
        $ret["l"] = $this->len;
        $ret["min_range"] =  $range;
        $ret["filter"] = $filter;
        $ret["grid"] = $grid;
        $encrModel = new PatternCryptModel();
        $encr = json_encode($ret);//$encrModel->encrpt(json_encode($ret));
        $this->sendOutput(
            $encr,
            array('Content-Type: application/json', 'HTTP/1.1 200 OK')
        );
        
    }
}
?>