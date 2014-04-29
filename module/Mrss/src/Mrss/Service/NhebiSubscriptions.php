<?php

namespace Mrss\Service;

class
NhebiSubscriptions
{
    /**
     * Configuration array pointing to each project
     *
     * @var array
     */
    protected $config = array();

    /**
     * We need to know which study is requesting the info
     *
     * @var
     */
    protected $currentStudyCode;

    public function setConfiguration($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfiguration()
    {
        return $this->config;
    }

    public function setCurrentStudyCode($code)
    {
        $this->currentStudyCode = $code;

        return $this;
    }

    public function getCurrentStudyCode()
    {
        return $this->currentStudyCode;
    }

    public function checkForDiscount($year, $ipeds)
    {
        if ($this->isEnabled()) {
            $results = $this->check($year, $ipeds);

            $count = 0;
            foreach ($results as $code => $result) {
                if ($result->subscribed) {
                    $count++;
                }
            }

            $discount = $this->getDiscountByCount($count);
        } else {
            $discount = 0;
        }


        return $discount;
    }

    /**
     * Loop over configuration, connecting to each app and getting subscription info
     *
     * @param $year
     * @param $ipeds
     * @return array
     * @throws \Exception
     */
    public function check($year, $ipeds)
    {
        $config = $this->getConfiguration();

        if (empty($config['apps'])) {
            throw new \Exception("NHEBI subscription check requires a config file");
        }

        $results = array();

        foreach ($config['apps'] as $code => $details) {
            // Skip this app itself
            if ($code == $this->getCurrentStudyCode()) {
                continue;
            }

            // Prepare to connect
            $url = $details['url'];

            // Append ipeds and year
            $url .= "?year=$year&ipeds=$ipeds";

            if ($this->getDebug()) {
                var_dump($url);
            }

            $results[$code] = $this->getRemoteResults($url);
        }

        if ($this->getDebug()) {
            var_dump($results);
        }

        return $results;
    }

    /**
     * Connect using cURL and return a PHP array
     *
     * @param $url
     * @return mixed
     */
    protected function getRemoteResults($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $json = curl_exec($ch);

        $result = json_decode($json);

        return $result;
    }

    public function getDiscountByCount($count)
    {
        // If count is 0, there's no discount
        $discount = 0;

        if (!empty($count)) {
            $config = $this->getConfiguration();
            $discountConfig = $config['discounts'];

            // If count == 1, return array element 0, etc
            $offset = $count - 1;
            if (!empty($discountConfig[$offset])) {
                $discount = $discountConfig[$offset];
            }
        }

        return $discount;
    }

    public function getDebug()
    {
        $config = $this->getConfiguration();

        return (!empty($config['debug']));
    }

    public function isEnabled()
    {
        $config = $this->getConfiguration();

        $enabled = !empty($config['enabled']);

        return $enabled;
    }
}
