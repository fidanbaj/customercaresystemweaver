<?php
namespace Application\Model;

class Ticket
{
   public $pk_ticket;
   public $fk_user;
   public $status;
   public $priority;
   public $subject;
   public $created;
   public $changed;

   public function exchangeArray($data)
   {
       $this->pk_ticket       = (!empty($data['pk_ticket'])) ? $data['pk_ticket'] : null;
       $this->fk_user         = (!empty($data['fk_user'])) ? $data['fk_user'] : null;
       $this->status          = (!empty($data['status'])) ? $data['status'] : null;
       $this->priority        = (!empty($data['priority'])) ? $data['priority'] : null;
       $this->subject         = (!empty($data['subject'])) ? $data['subject'] : null;
       $this->created         = (!empty($data['created'])) ? $data['created'] : null;
       $this->changed         = (!empty($data['changed'])) ? $data['changed'] : null;
   }
}
?>