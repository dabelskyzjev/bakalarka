<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of profilTask
 *
 * @author Jarda
 */
class ProfilePresenter extends BasePresenter {

    /** @var Todo\TaskRepository */
    private $taskRepository;

    /** @var Todo\UserRepository */
    private $userRepository;

    /** @var Todo\ResiteleRepository */
    public function inject(Todo\TaskRepository $taskRepository, Todo\UserRepository $userRepository) {
        $this->taskRepository = $taskRepository;
        $this->userRepository = $userRepository;
    }
    
    public function startup() {
        parent::startup();
    }
    
    public function actionUser($id) {
        if (isset($id)) {
            $profile = $this->userRepository->findBy(array('ID' => $id))->fetch();
            $profile->rating = $this->userRepository->getRating($id);
            $this->template->profile = $profile;
        } else
            $this->redirect('Homepage:default');
    }

    public function actionTask($id) {
        if (isset($id)) {
            $task = $this->taskRepository->profileTask($id);
                if ($task->splneno == 0) {
                    $task->splneno = "Ne";
                } else
                    $task->splneno = "Ano";
                if ($task->storno == 0) {
                    $task->storno = "Ne";
                } else
                    $task->storno = "Ano";
                if($task->zadavatel == $this->getUser()->getId()) {
                    $task->markAsDone = true;
                } else {$task->markAsDone = false;}
                $task->zadavatelID = $task->zadavatel;
                $task->zadavatel = $this->userRepository->findNameByID($task->zadavatelID);
                $task->resitelID = $task->resitel;
                if (isset($task->resitelID))
                    $task->resitel = $this->userRepository->findNameByID($task->resitel);
                
            $this->template->task = $task;
        } else
            $this->redirect('Homepage:default');
    }

}