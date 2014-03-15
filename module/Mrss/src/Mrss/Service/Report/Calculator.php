<?php

namespace Mrss\Service\Report;

class Calculator
{
    protected $data;

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Adapted from nccbpstats_percentile_spss() in NCCBP
     *
     * @param $percentile
     * @return float
     */
    public function getValueForPercentile($percentile)
    {
        $data = $this->getData();

        if(empty($data)){
            return null;
        }

        // makes sure that correct percentile is returned if n = 1
        if(count($data) == 1){
            return $data[0];
        }

        $percentile = $percentile / 100;

        sort($data);

        $n = count($data);

        $nonep = ($n+1) * $percentile;

        $j_integer = floor($nonep);
        $g_fraction = $nonep - $j_integer;


        if($j_integer == $n){
            $value = ((1 - $g_fraction) * $data[$j_integer - 1]) + ($g_fraction * ($data[$j_integer - 1]));
            return $value;
        }

        if($j_integer == 0){
            $value = ((1 - $g_fraction) * $data[$j_integer]) + ($g_fraction * $data[$j_integer]);
            return $value;
        }

        $value = ((1 - $g_fraction) * $data[$j_integer - 1]) + ($g_fraction * $data[$j_integer]);
        return $value;

    }

    /**
     * Adapted from NCCBP's percentile() function
     *
     * @param $value
     * @return float
     */
    public function getPercentileForValue($value)
    {
        $data = $this->getData();

        rsort($data);

        $n = count($data);

        $counter = 0;
        foreach($data as $val){
            if($val > $value){
                $counter++;
            }
        }

        $counter = $counter + 1;
        $percentile = (($n - $counter) / $n);

        return  $percentile * (100);
    }
}
