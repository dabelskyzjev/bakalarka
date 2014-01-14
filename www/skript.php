<?php
    function calculateHash($password, $salt = NULL) {
        if ($salt === null) {
            $salt = '$2a$07$';
        }
        return crypt($password, $salt);
    }
    
    echo calculateHash('admin');
?>
