<?php
namespace Application\Model;

class Enterprise
{
   public $pk_enterprise;
   public $name;
   public $address;
   public $zip;
   public $place;
   public $phone;
   public $created;
   public $changed;

   public function exchangeArray($data)
   {
       $this->pk_enterprise   = (!empty($data['pk_enterprise'])) ? $data['pk_enterprise'] : null;
       $this->name            = (!empty($data['name'])) ? $data['name'] : null;
       $this->address			    = (!empty($data['address'])) ? $data['address'] : null;
       $this->zip					    = (!empty($data['zip'])) ? $data['zip'] : null;
       $this->place           = (!empty($data['place'])) ? $data['place'] : null;
       $this->phone           = (!empty($data['phone'])) ? $data['phone'] : null;
       $this->created         = (!empty($data['created'])) ? $data['created'] : null;
       $this->changed         = (!empty($data['changed'])) ? $data['changed'] : null;
   }
}
?>