<?php
namespace Mrss\Model;

class College
{
    public $id;
    public $name;
    public $ipeds;
    public $city;
    public $latitude;
    public $longitude;

    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id'])) ? $data['id'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->ipeds  = (isset($data['ipeds'])) ? $data['ipeds'] : null;
        $this->city  = (isset($data['city'])) ? $data['city'] : null;
        $this->latitude  = (isset($data['latitude'])) ? $data['latitude'] : null;
        $this->longitude  = (isset($data['longitude'])) ? $data['longitude'] : null;
    }
}