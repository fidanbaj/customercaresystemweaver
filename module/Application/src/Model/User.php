<?php
namespace Application\Model;

class User
{
   public $pk_user;
   public $fk_enterprise;
   public $status;
   public $type;
   public $title;
   public $firstname;
   public $lastname;
   public $email;
   public $password;
   public $created;
   public $changed;
   public $deleted;

   public function exchangeArray($data)
   {
       $this->pk_user         = (!empty($data['pk_user'])) ? $data['pk_user'] : null;
       $this->fk_enterprise   = (!empty($data['fk_enterprise'])) ? $data['fk_enterprise'] : null;
       $this->status          = (!empty($data['status'])) ? $data['status'] : null;
       $this->type            = (!empty($data['type'])) ? $data['type'] : null;
       $this->title           = (!empty($data['title'])) ? $data['title'] : null;
       $this->firstname       = (!empty($data['firstname'])) ? $data['firstname'] : null;
       $this->lastname		    = (!empty($data['lastname'])) ? $data['lastname'] : null;
       $this->email           = (!empty($data['email'])) ? $data['email'] : null;
       $this->password        = (!empty($data['password'])) ? $data['password'] : null;
       $this->created         = (!empty($data['created'])) ? $data['created'] : null;
       $this->changed         = (!empty($data['changed'])) ? $data['changed'] : null;
       $this->deleted         = (!empty($data['deleted'])) ? $data['deleted'] : null;
   }
}
?>