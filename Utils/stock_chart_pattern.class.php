<?php
namespace Patterns;

class StockChartPatterns {
    /*
     * @param $filter - minimum row * cols filter to scan the grid by
    */
    public function __construct( array $dataset,$filters = 3,$wdow_size = 2,$step = 2){
        $this->dataset = $dataset;
        $this->filters = $filters;
    }
    /*
     * Will create seuence of 2x2 square 
     * cells values , based on start index and
     * len
     */
    public function constractModel($startIndex,$len,$gridRows = 2,$minRange = 0)
    {
       $model = [];
       $end = (($len+$startIndex) >= count($this->dataset) - ($gridRows- 1))?($len+$startIndex)-($gridRows- 1):($len+$startIndex);
       for ($i = $startIndex;$i < $end;$i++)
       {
         $grid = $this->createGrid($i,$gridRows,$gridRows,$minRange);
        // echo "--------------------------\n";
        // print_r($grid);
         //echo "---------[".$this->arraySumEven($grid)."]------------------\n";
         $model[] = $grid;//$this->arraySumEven($grid);
       }
       return $model;
    }
    function arraySumEven(array $grid)
    {
      $counter = 1;
      $sum = 0.0;
      $operator = "";
      $old_sum = 0;
      $bin = implode("", $grid);
      // Initialize the result variable
      $result = 0;
      /*
      // Use a loop to iterate over the elements in the array
      for ($i = 0; $i < count($grid); $i+=2) {
          // Check if the current position is even or odd
          if ($i % 2 == 0) {
              // If the position is even, add the element to the result
            $result += bindec($grid[$i].$grid[$i+1]/*.$grid[$i+2]* /);
          } else {
              // If the position is odd, subtract the element from the result
            $result -= bindec($grid[$i].$grid[$i+1]/*.$grid[$i+2]* /);
          }
      }
      echo "-------[".$bin."][".$result."]---------\n";
      */
      return $bin;//bindec($bin); 
      
    } 
    public function createGrid($startIndex,$len,$rows,$minRange = 0){
        $grid = array_fill(0,($rows**2),0);
        if (!is_array($this->dataset)|| count($this->dataset)==0)
         return $grid;
        if (($startIndex + $len) >= count($this->dataset))
          return [];
        // slice array to find max and min values , that will be top and bottom rows
        $arr = array_slice($this->dataset,$startIndex,$len);
        if (count($arr) == 0)
          return [];
       // echo "-------- dataset[".count($arr)."][".$startIndex."]------------------\n";
        //print_r($arr);
        $max = max($arr);
        $min = min($arr);
        $minRange = max(($max-$min),$minRange);
        $d_row = (($minRange/(int)$rows)); // calc column unit
        if ($d_row <= 0)
        {
          // echo "-----Start[".$startIndex."] len[".$len."]-----\n";
           //print_r($arr);
           // nothing to do here the values are the same
           return $grid;
        }
        $d_col = ((count($arr)/(int)$rows)); // calc row unit
        //echo "-------- min[".$min."]max[".$max."]COL[".$d_col."]ROW[".$d_row."]------------------\n";
        for ($i = 0;$i < count($arr);$i++)
        { // fill the grid with 1 and 0
            $row = ($minRange - ($max - $arr[$i]))/$d_row; // will give as real col
            $row = (round($row) == 0)?1:round($row);
            //$col += 1; // $i zero based
          //  echo "--------------- R[".$row."][".($max - $arr[$i])."][".$arr[$i]."]------------\n";
            $col = (($i+1)%$rows);//((($i+1)%$rows) != 0)?(($i+1)%$rows):$rows;
          //  echo "--------------- C[".$col."][".(($i+1)%$rows)."]----------------------------------------------\n";
            $cell = (($row*$rows))-$col;
            $grid[$cell-1] = 1;
            $row_1 = ($row - 1) > 0?$row - 1:0;
            if ($row_1 > 0 && $grid[(($row_1*$rows)-$col)-1] != 1)
              $grid[(($row_1*$rows)-$col)-1] = 0.5;
            $row_1 = ($row + 1) < $rows?$row +  1:0;
            if ($row_1 > 0 && $grid[(($row_1*$rows)-$col)-1] != 1)
              $grid[(($row_1*$rows)-$col)-1] = 0.5;
           // if ($col - 1 > 0)
           //   $grid[(($row*$rows)-($col-1))-1] = 0.25;
            //if ($col + 1 <= $rows)
            //  $grid[(($row*$rows)-$col+1)-1] = 0.25;

          //  echo "----------------- Cell[".($cell-1)."]---------------\n";
        }// end for
      //  echo "------------------ End -----------------\n";
        return $grid;
    }
   
    function createModelGrid($start,$len,$rows)
    {
        $grid = array_fill(0,((int)$rows*(int)$rows),0);
        if (!is_array($this->dataset)|| count($this->dataset)==0)
         return $grid;
        
        $arr = array_slice($this->dataset,$start,$len);
        $max = max($arr);
        $min = min($arr);
        $d_col = ($max-$min)/(int)$rows;
        $d_row = count($arr)/(int)$rows;
        for ($i = 0;$i < count($arr);$i++)
        {
          $col = ($max - $arr[$i])/(float)$d_col;
          $col = ($col == 0.0)?0.1:$col;
          $r = ceil(($i+1)/(float)$d_row);
          $cell = (((ceil($col) - 1)*$rows)+$r);
          $column = $cell % $rows;
          $row = floor($cell / $rows);
          $grid[$cell] = 1;
          if ($column > 1 && $grid[($cell - 1)] != 1)
          {
    
            $grid[($cell - 1)] = 0.25;
           // echo "--- DWON COL[".$column."] [".$cell."][".($cell - 1)."]-----\n";
          }
          if ($column < ($rows - 1) && $grid[($cell + 1)] != 1)
          {
    
            $grid[($cell + 1)] = 0.25;
          //  echo "--- UP COL[".$column."] [".$cell."][".($cell + 1)."]-----\n";
          }
          if ($row > 0 &&  $grid[($cell - $rows)] != 1)
          {  
            $grid[($cell - $rows)] = 0.5;
            //echo "--- TOP COL[".$row."] [".$cell."][".($cell - $rows)."]-----\n";
          }
          if ($row < ($rows - 1) && $grid[($cell + $rows)] != 1)
          {
            $grid[($cell + $rows)] = 0.5;
            //echo "--- BOTTOM COL[".$row."] [".$cell."][".($cell + $rows)."]-----\n";
          }
          if ($column > 1 && $row > 0 && ($grid[($cell - $rows) - 1] != 1 && $grid[($cell - $rows) - 1] != 0.5))
          {
            $grid[($cell - $rows) - 1] = 0.25;
           // echo "--- TOP LEFT[".$row."] [".$cell."][".(($cell - $rows) - 1)."]-----\n";
          }
          if ($column < ($rows - 1) && $row > 0 && ($grid[($cell - $rows) + 1] != 1 && $grid[($cell - $rows) + 1] != 0.5 ))
          {
            $grid[($cell - $rows) + 1] = 0.25;
           // echo "--- TOP RIGHT[".$row."] [".$cell."][".(($cell - $rows) + 1)."]-----\n";
          }
          //---------------
          if ($column > 1 && $row < ($rows - 1) && ($grid[($cell + $rows) - 1] != 1 && $grid[($cell + $rows) - 1] != 0.5))
          {
            $grid[($cell + $rows) - 1] = 0.25;
           // echo "--- BOTTOM LEFT[".$row."] [".$cell."][".(($cell + $rows) - 1)."]-----\n";
          }
          if ($column < ($rows - 1) && $row < ($rows - 1) && ($grid[($cell + $rows) + 1] != 1 && $grid[($cell + $rows) + 1] != 0.5))
          {
            $grid[($cell + $rows) + 1] = 0.25;
          //  echo "--- BOTTOM RIGHT[".$row."] [".$cell."][".(($cell + $rows) + 1)."]-----\n";
          }
          
        }
        $this->model = $grid;
        return $this->model;
    }
    function checkGridPatterns($patternStart,$len,$gridRows,$minMatch = 0.6,$predicted_len = 0)
    {
        if (!$this->model)
          $this->createModelGrid($patternStart,$len,$gridRows);
        //$model_grid = createModelGrid($a,$patternStart,$len,$gridRows);
        $foundAt = [];
        $grdCnt = 0;
        $predicted_len = ($patternStart + $len < $predicted_len)?$predicted_len - ($patternStart + $len):0;
        for ($j = $patternStart + $len + $predicted_len;$j < count($this->model)-$len;$j++)
      {
            $arr = array_slice($this->dataset,$j,$len);
            $max = max($arr);
            $min = min($arr);
            $d_col = ($max-$min)/(int)$gridRows;
            
            $d_row = count($arr)/(int)$gridRows;
            $accuracy = 0.0;
            for ($i = 0;$i < count($arr);$i++)
            {
                $col = ($max - $arr[$i])/(float)$d_col;
                $col = ($col == 0.0)?0.1:$col;
                $r = ceil(($i+1)/(float)$d_row);
                $cell = (((ceil($col) - 1)*$gridRows)+$r);
                
                $accuracy += $this->model[$cell];
            }
            $accuracy /= count($arr);
            if ($accuracy >= $minMatch)
            {
              /* if ($grdCnt < 1)
                {
                    $grd = createModelGrid($a,$j,$len,$gridRows);
                    printGrid($grd,$gridRows,"Compare[".$accuracy."]accuracy Grid");
                    $grdCnt++;
                }
                */
                $foundAt[] = $j;
                $j += $len;
            }
      }// end for
      return $foundAt;
    }
    /**
     * Depricated: replaced by 
     * @subGridToArray(array $grid,$rows,$cols,$startIndex,$sub_rows,$sub_cols,$step = 1,$print = false)
     * as more generic
     */
    function calcFilter(array $grid,$startIndex,$rows)
    {
        $sum = 0.0;
        $cols = 0;
        for ($i = 0;$i < $this->filters**2;$i++)
        {
            $incr = $i + $cols;
            $incr -= (((($i+$startIndex)%$rows) == 0) && (($i % $this->filters) != 0))?1:0;
            $sum += $grid[$startIndex + $incr];
            $cols += ((($i % $this->filters) == 0) || ((($i+$startIndex)%$rows) == 0))?$rows-$this->filters:0;            
        }
        return ($sum / ($this->filters**2));
    }
    
    function applyFilters(array $grid,$rows,$step = 1)
    {
      $reduce_grid = [];
      //$tmp = [-1, 1];
      //$tmp_1 = [1, -1];
      //echo "------[".($this->arraySumEven($tmp))."] - [" .($this->arraySumEven($tmp_1))."]------\n";
      for ($i= 0;$i < count($grid);$i+= $step)
      {
        $r = ($rows - ceil(($i+1)/$rows));
        //$reduce_grid[] = $this->calcFilter($grid,$i,$rows);
        $grd = $this->subGridToArray($grid,$rows,$rows,$i,$this->filters,$this->filters,1/*,($i < 4)*/);
        $sum_even = $this->arraySumEven($grd);
        //$sum_norm = array_sum($grd);
       // echo "------------------ Sum Even[".$sum_even."] Norm[".$sum_norm."]-----------\n";
        //print_r($grd);
        $reduce_grid[] = /*array_sum*/$sum_even/(/*$this->filters**2*/count($grd));
        if (($rows - ($i % $rows)) < $this->filters)
        {    
          //  echo "----------------[".$i."]-------\n";
            $i += (($rows - ($i % $rows))*$step);
            if ($r < $this->filters)
             break;
        }
        
      }
      return $reduce_grid;
    }
    /*
     * Select subgrid inside the grid and
     * present the 1d array
     */
    function subGridToArray(array $grid,$rows,$cols,$startIndex,$sub_rows,$sub_cols,$step = 1,$print = false)
    {
       $sub = [];
       $max_r = $startIndex + ($rows*($sub_rows-1));
       for ($i = $startIndex;$i < $max_r || $sub_rows > 0;$i+=($rows*$step),$sub_rows-= $step)
       {
          $l = (($rows - ($i % $rows)) < $sub_cols)?($rows - ($i % $rows)):$sub_cols;
          $c =  array_slice($grid,$i,$l); 
          $sub = array_merge($sub,$c);
          if ($print)
            echo "------------ start at[".$i."]-[".($i+$l)."]---------\n";
          //print_r($sub);
          /*if ((($rows - ($i % $rows)) < $sub_cols))
          {
            $r = ($rows - ceil(($i+1)/$rows));
            if ($r < $sub_rows)
             break;
          }*/
       }
       if ($print)
        print_r($sub);
       return $sub;
    }
    function applyPooling(array $grid,$rows,$cols,$w_size = 2,$step = 1)
    {
        $reduce_grid = [];
        for ($i= 0;$i < count($grid);$i+=$step)
        {
          $r = ($rows - ceil(($i+1)/$rows));
          $p = $this->subGridToArray($grid,$rows,$rows,$i,$w_size,$w_size/*,1,($i < 1)*/);
          //if ($i < 1)
          //  print_r($p);
          $reduce_grid[] = max( $p);
          if (($rows - ($i % $rows)) < $this->filters)
          {    
              //echo "----------------[".$i."]-------\n";
              $i += (($rows - ($i % $rows))*$step);
              if ($r < $w_size)
               break;
          }
          
        }
        return $reduce_grid;
    }
}
?>