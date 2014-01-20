<?php

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter {

    /** @var Todo\TaskRepository */
    private $taskRepository;
    //private $pripominkyRepository;
    private $userRepository;
    
    private $resiteleRepository;
    
    public function startup() {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');   
        }
        $task=$this->taskRepository->pocetPoslednich(5);
        foreach ($task as $t) {   foreach ($task as $t) {
            $t->popis = $this->taskRepository->zkratitPopis($t->popis);
            $t->jizzabrano = $this->resiteleRepository->isZabrano($t->ID, $this->getUser()->getId());
            $t->jsemVlastnik = $this->taskRepository->jsemVlastnik($t->ID);
            $t->zadavatelID = $t->zadavatel;
            $t->zadavatel = $t->ref('user', 'zadavatel')->username;
            $t->resitelID = $t->resitel;
            if ($t->resitel != null)
                $t->resitel = $t->ref('user', 'resitel')->username;
        }
        }
        $this->template->posledni = $task;
        
        $i=0;
        if ($this->userRepository->getTypUctu() == 1) {
            $task = $this->taskRepository->findIncompleteByUser($this->getUser()->getId())->order(('datum DESC'));
            foreach ($task as $t) {
                $i++;
                $t->popis = $this->taskRepository->zkratitPopis($t->popis);
                $t->zadavatelID = $t->zadavatel;
                $t->resitelID = $t->resitel;
                $t->zadavatel = $t->ref('user', 'zadavatel')->username;
                $t->resitel = $t->ref('user', 'resitel')->username;
            }
        } else {
            $task = $this->taskRepository->findIncompleteByOwner($this->getUser()->getId())->order('datum DESC');
            foreach ($task as $t) {
                $i++;
                $t->popis = $this->taskRepository->zkratitPopis($t->popis);
                $t->zadavatelID = 0;
                $t->zadavatelID = $t->zadavatel;
                $t->zadavatel = $t->ref('user', 'zadavatel')->username;
                $t->resitelID = 0;
                $t->resitelID = $t->resitel;
                if ($t->resitel != NULL)
                    $t->resitel = $t->ref('user', 'resitel')->username;
            }
        }
            if($i>0) $i=true;
            $this->template->cykly = $i;
            $this->template->tasks = $task;
    }

    public function inject(Todo\TaskRepository $taskRepository, Todo\UserRepository $userRepository, Todo\ResiteleRepository $resiteleRepository) {
        $this->taskRepository = $taskRepository;
        $this->userRepository = $userRepository;
        $this->resiteleRepository = $resiteleRepository;
    }
    
}
