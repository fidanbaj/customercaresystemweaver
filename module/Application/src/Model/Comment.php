<?php
namespace Application\Model;

class Comment
{
   public $pk_comment;
   public $fk_user;
   public $fk_ticket;
   public $message;
   public $created;

   public function exchangeArray($data)
   {
       $this->pk_comment      = (!empty($data['pk_comment'])) ? $data['pk_comment'] : null;
       $this->fk_user         = (!empty($data['fk_user'])) ? $data['fk_user'] : null;
       $this->fk_ticket       = (!empty($data['fk_ticket'])) ? $data['fk_ticket'] : null;
       $this->message         = (!empty($data['message'])) ? $data['message'] : null;
       $this->subject         = (!empty($data['subject'])) ? $data['subject'] : null;
       $this->created         = (!empty($data['created'])) ? $data['created'] : null;
   }
}
?>