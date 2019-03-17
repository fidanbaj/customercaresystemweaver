<?php
 namespace Application\Model;

 use Zend\Db\TableGateway\TableGatewayInterface;

 class UserTable
 {
    protected $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->order('deleted ESC, status ESC, firstname ESC');
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function fetchAllCustomer()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->order('deleted ESC, status ESC, firstname ESC');
        $select->where->like('type', 1);
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getById(int $id)
    {
        $current = $this->tableGateway->select(['pk_user' => $id]);
        return $current->current();
    }

    public function getByEnterpriseId(int $id)
    {
        $current = $this->tableGateway->select(['fk_enterprise' => $id]);
        return $current->current();
    }

    public function getByMail(string $mail)
    {
        $current = $this->tableGateway->select(['email' => $mail]);
        return $current->current();
    }

    public function insert($data)
    {
        $result = $this->tableGateway->insert($data);
        return $this->tableGateway->lastInsertValue;
    }

    public function update($id, $data)
    {
        $this->tableGateway->update($data, ['pk_user' => $id]);
    }
 }
?>