<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Todo;
use Nette;

/**
 * Description of pripominky
 *
 * @author Jarda
 */
class PripominkyRepository extends Repository {
    /**
     * 
     * @param int $ukol
     * @param int $hodnotici
     * @param int $cil
     * @param double $hodnoceni
     * @param string $popis
     */
    
    public function pridatHodnoceni($ukol, $hodnotici, $cil, $hodnoceni, $popis) {
        return $this->getTable()->insert(array(
                'ukol' => $ukol,
                'pripominajici' => $hodnotici,
                'cil' => $cil,
                'hodnoceni' => $hodnoceni,
                'popis' => $popis,
                'datum' => new Nette\DateTime()
                ));
    }
}

