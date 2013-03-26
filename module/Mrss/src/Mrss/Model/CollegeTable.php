<?php
namespace Mrss\Model;

use Zend\Db\TableGateway\TableGateway;

class CollegeTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getCollege($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveCollege(College $college)
    {
        $data = array(
            'name' => $college->name,
            'ipeds'  => $college->ipeds,
            'city'  => $college->city,
            'latitude'  => $college->latitude,
            'longitude'  => $college->longitude,
        );

        $id = (int)$college->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getCollege($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteCollege($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}
