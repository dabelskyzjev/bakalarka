<?php

use Nette\Security,
    Nette\Utils\Strings;

use Nette\Security as NS;
/*
  CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  password char(60) NOT NULL,
  role varchar(20) NOT NULL,
  PRIMARY KEY (id)
  );
 */

/**
 * Users authenticator.
 */
class Authenticator extends Nette\Object implements Security\IAuthenticator {

    /** @var Nette\Database\Connection */
    private $database;
    
    
    
    public function __construct(Nette\Database\Connection $database) {
        $this->database = $database;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($username, $password) = $credentials;
        $row = $this->database->table('user')->where('username', $username)->fetch();

        if (!$row) {
            echo "<h2>Zadali jste špatné jméno</h2>";
        } else

        if ($row->password !== self::calculateHash($password, $row->password)) {
            throw new NS\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
        }

        unset($row->password);
        return new NS\Identity($row->id, $row->rules, $row->toArray());
    }

    /**
     * Computes salted password hash.
     * @param  string
     * @return string
     */
    public static function calculateHash($password, $salt = NULL) {
        if ($password === Strings::upper($password)) { // perhaps caps lock is on
            $password = Strings::lower($password);
        }
        return crypt($password, $salt ? : '$2a$07$' . Strings::random(22));
    }

}
