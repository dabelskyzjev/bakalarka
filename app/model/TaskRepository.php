<?php

namespace Todo;

use Nette;

class TaskRepository extends Repository {
    private $db;
    public function __construct(Nette\Database\Connection $db) {
        parent::__construct($db);
        $this->db = $db;
    }
    /**
     * Vrací seznam nehotových úkolů.
     * @return Nette\Database\Table\Selection
     */


    public function findIncomplete() {
        return $this->findAll()->where(array('splneno' => 0));
    }

    /**
     * @param int $userID ID Ownera ticketu
     */
    
    public function findMyTasks($userID) {
        return $this->findIncomplete()->where(array('zadavatel' => $userID));
    }
    
    public function findIncompleteByUser($userId) {
       return $this->findIncomplete()->where(array('resitel' => $userId));
    }
    
    public function findIncompleteByOwner($userId) {
       return $this->findIncomplete()->where(array('zadavatel' => $userId));
    }

    public function findComplete($userId) {
        return $this->getTable()->where('zadavatel = ? OR resitel = ?',$userId,$userId)->order('datum DESC');
    }
    
    /**
     * @param int $idTask ID Tasku
     * @return boolean Pokud jsem vlastník, vrátí true, jinak false
     */
    
    public function jsemVlastnik($idTask) {
        $id = $this->getTable()
                ->select('zadavatel')
                ->where(array('ID' => $idTask))->fetch();
        if($id->zadavatel == \Nette\Environment::getUser()->getId()) 
        return true;
        else return false;
    }
    
    public function vybratResitele($taskId, $resitelID) {
        return $this->findBy((array('ID' => $taskId)))
                ->update(array('resitel' => $resitelID));
    }
    
    /**
     * @param string $nazev
     * @param string $popis
     * @param int $zadavatel
     * @return Nette\Database\Table\ActiveRow
     */
    
    public function createNewTask($nazev, $popis, $zadavatel) {
        return $this->getTable()->insert(array(
                    'nazev' => $nazev,
                    'popis' => $popis,
                    'zadavatel' => $zadavatel,
                    'datum' => new \DateTime()
                ));
    }
    
    public function MarkAsDone($taskId) {
        return $this->getTable()->where(array('ID' => $taskId))->update(array('splneno' => 1));
    }

    public function zkratitPopis($popis) {
        if (strlen($popis) >= 50) {
            $popis = substr($popis, 0, 50) . "...";
        }
        return $popis;
    }
    
    /**
     * 
     * @return Nette/Database/Table
     */
    
    public function countOfTasks() {
        return $this->getTable()->select('ID')->count();
    }
    
    /**
     * 
     * @param int $count
     * @return array
     */
    
    public function pocetPoslednich($count) {
        $dostupne = $this->getTable()
                ->where(array('splneno' => 0,
                              'storno' => 0,
                              'resitel' => NULL))
                ->order('datum DESC')
                ->limit($count);
        return $dostupne;
    }
    
    public function profileTask($id) {
        return $this->db->table('task')->where(array('ID' => $id))->fetch();
    }
    
    public function maViceResitelu($taskid) {
        $bool = $this->profileTask($taskid);
          if($bool->viceresitelu == 0) {
            return false;
        } else return true;
    }
}
