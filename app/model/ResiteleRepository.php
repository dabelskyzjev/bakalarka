<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Todo;
use Nette;
/**
 * Description of ResiteleRepository
 *
 * @author Jarda
 */
class ResiteleRepository extends Repository{
    
    /**
     * 
     * @param int $taskID
     * @return array of ints
     */
    private $db;
    
    public function __construct(Nette\Database\Connection $db) {
        parent::__construct($db);
        $this->db = $db;
    }
    
    public function findResitele($taskID) {
        return $this->findBy(array('ukol' => $taskID));
    }
    
    public function findZabraneUkoly($userId) {
        return $this->findAll()->where(array('resitel' => $userId));
      //  return $this->db->table('task')->where(array('resitel' => NULL));
    }
    
    /**
     * @param int taskId
     * @param int userId
     * @return Nette\Database\Table\ActiveRow
     */
    
    public function zabratUkol($userId, $taskId) {
        return $this->getTable()->insert(array(
                   'ukol' => $taskId,
                   'resitel' => $userId
                ));
    }
    
    /**
     * @param int $userId ID uzivatele
     * @param int $taskId ID tasku
     * @return Nette\Datebase\Table\ActiveRow
     */
    
    public function odebratUkol($userId, $taskId) {
        return $this->getTable()
                ->where(array(
                        'ukol' => $taskId,
                        'resitel' => $userId,
                        ))
                ->delete();
    }
    
    /**
     * @param int $ukolId ID Tasku
     * @param int $resitelId ID Resitele
     * @return true / false
     * 
     */
    
    public function isZabrano($ukolId, $resitelId) {
       $task = $this->findZabraneUkoly($resitelId)->where(array('ukol' => $ukolId))->fetch();
       if(isset($task->ukol) == NULL) {
           return false;
       }
       return true;
    }
    
    public function vymazatPrebytecneRezervace($taskId) {
        return $this->getTable()
                ->where(array(
                        'ukol' => $taskId
                        ))
                ->delete();
    }
    
    public function countOfResitele($taskId) {
        return $this->findResitele($taskId)->count();
    }
    
    public function pocetResiteluUlohy($taskId) {
        return $this->db->table('viceresitelu')->where(array('task_id' => $taskId))->count();
    }
    
    public function vybratResiteleUlohy($taskId) {
        return $this->db->table('viceresitelu')->where(array('task_id' => $taskId));
    }
}
