<?php

namespace Todo;

use Nette;

class UserRepository extends Repository {
    private $db;
    public function __construct(Nette\Database\Connection $db) {
        parent::__construct($db);
        $this->db = $db;
    }
    /**
     * @return Nette\Database\Table\ActiveRow
     */
    public function findNameByID($id) {
        $pole = $this->findAll()->where(array('ID' => $id))->fetch();
        $pole = $pole->username;
        return $pole;
    }
    
    public function findByName($username) {
        return $this->findBy(array('username' => $username))->fetch();
    }

    public function setPassword($id, $password) {
       return $this->getTable()->where(array('id' => $id))->update(array(
            'password' => \Authenticator::calculateHash($password)
        ));
    }
    
    public function zmenPodpis($userId, $podpis) {
        return $this->getTable()->where(array('ID' => $userId))->update(array('podpis' => $podpis));
    }
    
    public function getRating($userId) {
        $foo = $this->db->table('rating')->select('rating')->where(array('ID' => $userId))->fetch();
        return $foo->rating;
    }

    public function getTypUctu() {
        $role = Nette\Environment::getUser()->getRoles();
        return $role[0];
    }

}
