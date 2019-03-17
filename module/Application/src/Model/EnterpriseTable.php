<?php
 namespace Application\Model;

 use Zend\Db\TableGateway\TableGatewayInterface;

 class EnterpriseTable
 {
    protected $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->order('name ESC');
        $select->where->notEqualTo('pk_enterprise', 1);
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getById(int $id)
    {
        $current = $this->tableGateway->select(['pk_enterprise' => $id]);
        return $current->current();
    }

    public function getByName(string $name)
    {
        $current = $this->tableGateway->select(['name' => $name]);
        return $current->current();
    }

    public function insert($data)
    {
        $result = $this->tableGateway->insert($data);
        return $this->tableGateway->lastInsertValue;
    }

    public function update($id, $data)
    {
        $this->tableGateway->update($data, ['pk_enterprise' => $id]);
    }
 }
?>