<?php
namespace Todo;
use Nette;

class RegistraceRepository extends Repository {
    private $database;
    
    public function __construct(Nette\Database\Connection $db) {
        parent::__construct($db);
        $this->database = $db;
    }
    
    public function createPerson($username, $password, $role, $email) {
        return $this->database->table('user')->insert(array(
                    'username' => $username,
                    'password' => \Authenticator::calculateHash($password),
                    'name' => $username,
                    'email' => $email,
                    'rules' => $role,
                    'datum' => new Nette\DateTime()));
    }
}
