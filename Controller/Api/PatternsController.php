<?php
namespace api\Controller;

require_once(PROJECT_ROOT_PATH."Utils/CsvUtils.class.php");
require_once(PROJECT_ROOT_PATH."Utils/stock_chart_pattern.class.php");
require_once(PROJECT_ROOT_PATH."Utils/Chartdata.class.php");
require_once(PROJECT_ROOT_PATH."Utils/Statistics.php");
require_once(PROJECT_ROOT_PATH."Utils/pattern_encrypt_decrypt.php");
use Patterns\StockChartPatterns;
use Patterns\Chart;
use Utils\CsvUtils;
use Patterns\Statistics;
use Patterns\PatternCryptModel;

class PatternsController extends BaseController
{
    public function readCsvFile()
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
        $this->col_no = (!isset($arrQueryStringParams['col_no']))?"0":trim($arrQueryStringParams["col_no"],"\"'");//$_GET['col_no'];
        $this->startIndex = (!isset($arrQueryStringParams['strt_indx']))?"1":trim($arrQueryStringParams["strt_indx"],"\"'");
        $this->len = (!isset($arrQueryStringParams['l']))?"5":trim($arrQueryStringParams["l"],"\"'");
        $reverse_read = (isset($arrQueryStringParams['reverse_read'])? true:false);
        $header = (isset($arrQueryStringParams['header'])? true : false);
        //$ma = (!isset($_GET['ma']))?21:trim($_GET["ma"],"\"'");
        $url = trim($arrQueryStringParams["s"],"\"'");
        $this->$scv = new CsvUtils();
        $this->data = $this->$scv->google_sheet_read_csv($url,-1,$header,$reverse_read);
        if ( $this->data &&  $this->data["err"])
        {
           $strErrorDesc =  $this->data;
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
            array('Content-Type: application/json', $strErrorHeader)
            );
        }
       // echo "---------------------------[".($this->col_no)."]---------\n";
        $this->columnData = array_column($this->data,(int)($this->col_no));
       
    }

    public function findAction()
    {
        $this->readCsvFile();
        $arrQueryStringParams = $this->getQueryStringParams();
        $model_str = $arrQueryStringParams['model'];
        if (!$model_str)
        {
            $strErrorDesc =  "Missing Model to searchfor patterns.";
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
            array('Content-Type: application/json', $strErrorHeader)
            );
        }
        $model = json_decode($model_str);
        $patternIndex = 0;
        $startPatternIndex = 0;
        $found = [];
        //print_r($model);
        $this->$charts = new StockChartPatterns($this->columnData);
        print_r($model->grid);
        //echo "----[".intval($model->filter)."] ---[".intval($model->min_range)."]-----\n";
        //for ($i = ($this->startIndex + $this->len);$i < (count($this->columnData)-($this->len));$i++)
        for ($i = ($this->startIndex);$i < (($this->startIndex) + ($this->len))+1;$i++)
        {
            $grid = $this->$charts->createGrid($i,intval($model->filter),intval($model->filter),intval($model->min_range));
           // echo "---------before break--------[".$i."]-------------------------\n";
            //print_r($grid);
            if (count($grid) == 0)
                break;
            $grid = $this->$charts->arraySumEven($grid);
            echo "-----------------[".$i."]-------------------------<br/>";
            print_r($grid);
            if ($model->grid[$patternIndex] == $grid && $patternIndex == 0)
            {
                $startPatternIndex = $i;
                //$i += intval($model->filter) - 1;
            }else
            if ($model->grid[$patternIndex] != $grid
                && $model->grid[$patternIndex+1] != $grid
                && (count($model->grid)-1) > $patternIndex)
                {
                    $patternIndex = 0;
                    $startPatternIndex = 0;
                }else if ($model->grid[$patternIndex+1] == $grid)
                {
                   $patternIndex++;
                  // $i += intval($model->filter) - 1;
                }
                if ($model->grid[$patternIndex] != $grid
                && $model->grid[$patternIndex+1] != $grid
                && (count($model->grid)-1) <= $patternIndex )
                {
                    //$i -= intval($model->filter)-1;
                   $found[] = [$startPatternIndex,$i - $startPatternIndex];
                }

        }// end for
        $encr = json_encode($found);//$encrModel->encrpt(json_encode($ret));
        $this->sendOutput(
            $encr,
            array('Content-Type: application/json', 'HTTP/1.1 200 OK')
        );
    }
    public function modelAction()
    {
        $this->readCsvFile();
        $arrQueryStringParams = $this->getQueryStringParams();
        // This is a range min - max of selected candles range.
        // if set more than 0 this will be used to calculate rows = (max-min)
        // of the grid, otherwise will use max and min of candle range
        $range = (isset($arrQueryStringParams['min_range'])? trim($arrQueryStringParams['min_range'],"\"'"):0);
        // this is filter that will break the grid on smaller subgrids , default , would be 2 , which make 
        // subgrid of 2 x 2 = 4 cells , if user add 3 mean subgrid of 3 x 3 = 9 cells and so on ,
        // this will be subgrid that will filter big grid define by ($this->len ** 2) = grid cells
        // the retrun will be reduce order of array that contains pattern sequence
        $filter = (isset($arrQueryStringParams['filter'])? trim($arrQueryStringParams['filter'],"\"'"):2);
        //$this->gridRows = (!isset($arrQueryStringParams['min_efficiency']))?"5":trim($arrQueryStringParams["min_efficiency"],"\"'");
        //$accuracy = (!isset($_GET['min_accuracy']))?"0.5":trim($_GET["min_accuracy"],"\"'");
       
        $this->chart = new Chart();
        $ma = (!isset($_GET['ma']))?21:trim($_GET["ma"],"\"'");
        $ma_arr = [];
        $stat = new Statistics();
        $stat->moving_average($this->columnData,$ma,$ma_arr);
        //print_r($this->columnData );
        $this->$charts = new StockChartPatterns($ma_arr);
        $grid = $this->$charts->constractModel($this->startIndex,$this->len,$filter,$range);
        //print_r($grid);
        
        //$grid = $this->$charts->createModelGrid($startIndx,$this->len,$this->gridRows);
        $ret["col_no"] = $this->col_no;
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