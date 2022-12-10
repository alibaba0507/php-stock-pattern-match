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
    }
}
?>