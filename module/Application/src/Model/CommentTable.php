<?php
 namespace Application\Model;

 use Zend\Db\TableGateway\TableGatewayInterface;

 class CommentTable
 {
    protected $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getByTicketId(int $ticketId)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->order('created ESC');
        $select->where->like('fk_ticket', $ticketId);
        $resultSet = $this->tableGateway->selectWith($select);

        return $resultSet;
    }

    public function insert($data)
    {
        $result = $this->tableGateway->insert($data);
        return $this->tableGateway->lastInsertValue;
    }
 }
?>