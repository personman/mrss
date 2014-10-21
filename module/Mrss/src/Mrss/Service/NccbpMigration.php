<?php

namespace Mrss\Service;

use Zend\Http\Client;
use Zend\Http\Cookies;
use Zend\Http\Request;

class NccbpMigration
{
    protected $oldUrl = "http://nccbp.dan.com";

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Cookies
     */
    protected $cookies;

    public function getOldReport($year = 2014)
    {
        $this->logIn();

        $url = $this->oldUrl . '/benchmark';
        //$this->client = new Client($this->oldUrl);

        $this->client->setUri($url);
        $this->client->setMethod('GET');

        $response = $this->client->send();
        echo $response->getBody();
        die;

        return $response;
    }

    public function logIn()
    {
        $loginUrl = $this->oldUrl . '/user/login';
        $this->client = new Client($loginUrl);
        $this->client->setOptions(
            array(
                'maxredirects' => 0,
                'timeout' => 600,
                //'keepalive' => true
            )
        );

        $this->client->setMethod('POST');
        $this->client->setParameterPost(
            array(
                'name' => 'admin',
                'pass' => 'p0w3r#U$3r_',
                'form_id' => 'user_login',
                //'form_build_id' => 'form-451fbfdd1f94b137379da2ec50d7717c',
                'op' => 'User Login'
            )
        );

        $response = $this->client->send();

        $this->cookies = new Cookies();
        $this->cookies->addCookiesFromResponse($response, $this->oldUrl);
        $this->client->setCookies(
            $this->cookies->getMatchingCookies($this->oldUrl)
        );


        if (true || !$response->isSuccess()) {
            //echo $response->getBody(); die;
        }
    }
}
