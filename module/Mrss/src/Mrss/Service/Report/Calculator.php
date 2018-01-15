<?php

namespace Mrss\Service\Report;

class Calculator
{
    protected $data;

    public function __construct($data = null)
    {
        $this->setData($data);
    }

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

        if (empty($data)) {
            return null;
        }

        // makes sure that correct percentile is returned if n = 1
        if (count($data) == 1) {
            return current($data);
        }

        // Handle min and max
        if ($percentile === 0) {
            return min($data);
        } elseif ($percentile == 100) {
            return max($data);
        }

        $percentile = $percentile / 100;

        sort($data);

        $n = count($data);

        $nonep = ($n+1) * $percentile;

        $j_integer = intval(floor($nonep));
        $g_fraction = $nonep - $j_integer;


        if ($j_integer == $n) {
            $value = ((1 - $g_fraction) * $data[$j_integer - 1]) + ($g_fraction * ($data[$j_integer - 1]));
            return $value;
        }

        if ($j_integer == 0) {
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
        //var_dump($data);
        foreach ($data as $val) {
            if ($val > $value) {
                $counter++;
            }
        }

        $counter = $counter + 1;
        $percentile = (($n - $counter) / $n);

        return  $percentile * (100);
    }

    public function getOutliers()
    {
        // Find values greater than 2 standard deviations from the mean
        $thresholdInStdDev = 2;

        $mean = $this->getMean();
        $stdDev = $this->getStandardDeviation();
        $threshold = $stdDev * $thresholdInStdDev;
        $outliers = array();

        foreach ($this->getData() as $college => $datum) {
            $difference = abs($datum - $mean);

            if ($difference > $threshold) {
                // We have an outlier, but what kind?
                if ($datum > $mean) {
                    $problem = 'high';
                } else {
                    $problem = 'low';
                }

                $outliers[] = array(
                    'college' => $college,
                    'value' => $datum,
                    'problem' => $problem
                );
            }
        }

        return $outliers;
    }

    /**
     * Convenience method
     */
    public function getMedian()
    {
        return $this->getValueForPercentile(50);
    }

    public function getMin()
    {
        return min($this->getData());
    }

    public function getMax()
    {
        return max($this->getData());
    }

    public function getCount()
    {
        return count($this->getData());
    }

    public function getMean()
    {
        $sum = array_sum($this->getData());
        $count = $this->getCount();

        $mean = $sum / $count;

        return $mean;
    }

    public function getStandardDeviation()
    {
        $mean = $this->getMean();
        $variances = 0.0;

        foreach ($this->getData() as $datum) {
            $variances += pow($datum - $mean, 2);
        }

        $variance = $variances / $this->getCount();
        $standardDeviation = sqrt($variance);

        return $standardDeviation;
    }
}
