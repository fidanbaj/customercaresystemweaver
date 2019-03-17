<?php
 namespace Application\Model;

 use Zend\Db\TableGateway\TableGatewayInterface;

 class TicketTable
 {
    protected $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->order('changed DESC, priority ESC, subject ESC');
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getByUserId(int $userId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->order('changed DESC, priority ESC, subject ESC');
        $select->where->like('fk_user', $userId);
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function getById(int $id)
    {
        $current = $this->tableGateway->select(['pk_ticket' => $id]);
        return $current->current();
    }

    public function insert($data)
    {
        $result = $this->tableGateway->insert($data);
        return $this->tableGateway->lastInsertValue;
    }

    public function update($id, $data)
    {
        $this->tableGateway->update($data, ['pk_ticket' => $id]);
    }
 }
?>