<?php

use Nette\Application\UI\Form;

/**
 * Presenter, který zajišťuje výpis seznamů úkolů.
 *
 * @property callable $taskFormSubmitted
 */
class TaskPresenter extends BasePresenter {
    /** @var Todo\TaskRepository */
    private $taskRepository;

    /** @var Todo\UserRepository */
    private $userRepository;

    /** @var Todo\ResiteleRepository */
    private $resiteleRepository;

    /** @var Todo\PripominkyRepository */
    private $pripominkyRepository;
    
    /** @var Nette\Database\Table\ActiveRow */
    public function startup() {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

    public function inject(Todo\TaskRepository $taskRepository, 
            Todo\UserRepository $userRepository, 
            Todo\ResiteleRepository $resiteleRepository,
            Todo\PripominkyRepository $pripominkyRepository) {
        $this->taskRepository = $taskRepository;
        $this->userRepository = $userRepository;
        $this->resiteleRepository = $resiteleRepository;
        $this->pripominkyRepository = $pripominkyRepository;
    }

    public function makeNavigace() {
        $c = $this->taskRepository->countOfTasks();
        $pom=0;
        $navigace = array();
        for ($i = 0; $i < $c; $i++) {
            if ($i % 10 == 0) {
                $navigace[$pom] = $pom;
                $pom++;
            }
        }
        return $navigace;
    }

    
    public function renderBrowse($todo) {
        $this->template->navigace = $this->makeNavigace();
        if($todo != NULL) $this->template->todo = $todo;
        else $this->template->todo = 0;
        $task = $this->taskRepository->findIncomplete()->where(array('storno' => 0))->order(('datum DESC'))->limit(10, $todo * 10);
        foreach ($task as $t) {
            if($this->taskRepository->maViceResitelu($t->ID)) {
                $t->vice = true;
                $t->resiteleID = $this->resiteleRepository->vybratResiteleUlohy($t->ID);
                $t->resitele = $t->resiteleID;
                foreach ($t->resitele as $res) {
                //    dump($res);
                }
            } else $t->vice = false;
            $t->popis = $this->taskRepository->zkratitPopis($t->popis);
            $t->jizzabrano = $this->resiteleRepository->isZabrano($t->ID, $this->getUser()->getId());
            $t->jsemVlastnik = $this->taskRepository->jsemVlastnik($t->ID);
            $t->zadavatelID = $t->zadavatel;
            $t->zadavatel = $t->ref('user', 'zadavatel')->username;
            $t->resitelID = $t->resitel;
            if ($t->resitel != null)
                $t->resitel = $t->ref('user', 'resitel')->username;
        }
        $this->template->tasks = $task;
    }

    public function renderZabrane() {
        $pole = $this->resiteleRepository->findZabraneUkoly($this->getUser()->getId())->order('ukol DESC');
        foreach ($pole as $t) {
            $t->ID = $t->ukol;
            $t->datum = $t->ref('task', 'ukol')->datum;
            $t->popis = $t->ref('task', 'ukol')->popis;
            $t->nazev = $t->ref('task', 'ukol')->nazev;
            $t->popis = $this->taskRepository->zkratitPopis($t->popis);
            $t->zadavatelID = $t->ref('task', 'ukol')->zadavatel;
            $t->zadavatel = $this->userRepository->findNameByID($t->zadavatelID);
        }
        $this->template->tasks = $pole;
    }

    public function actionDefault($todo, $id) {
        if (isset($todo) && ($todo == "oznacit") && isset($id)) {
            if ($this->taskRepository->jsemVlastnik($id)) {
                $this->taskRepository->MarkAsDone($id);
                $this->flashMessage("Úkol byl úspěšně označen jako splněný!", 'success');
            }
        }
    }

    public function actionZabrane($todo, $id) {
        if(isset($todo) && ($todo=="odebrat") && isset($id)) {
            $this->resiteleRepository->odebratUkol($this->getUser()->getId(), $id);
            $this->flashMessage('Úspěšně jste se odepsali z úkolu!', 'success');
            $this->redirect('Task:zabrane');
        }
    }
    
    public function actionBrowse($todo, $id) {
        if (($todo === 'zabrat') && (isset($id))) {
            if (!$this->resiteleRepository->isZabrano($id, $this->getUser()->getId())) {
                $this->resiteleRepository->zabratUkol($this->getUser()->getId(), $id);
                $this->flashMessage('Úspěšně jste zabrali úkol!', 'success');
            }
        }
        if (($todo === 'odebrat') && (isset($id))) {
            $this->resiteleRepository->odebratUkol($this->getUser()->getId(), $id);
            $this->flashMessage('Úspěšně jste se odepsali z úkolu!', 'success');
        }
    }

    public function renderDefault() {
        if ($this->userRepository->getTypUctu() == 1) {
            $task = $this->taskRepository->findIncompleteByUser($this->getUser()->getId())->order(('datum DESC'));
            foreach ($task as $t) {
                $t->popis = $this->taskRepository->zkratitPopis($t->popis);
                $t->zadavatelID = $t->zadavatel;
                $t->resitelID = $t->resitel;
                $t->zadavatel = $t->ref('user', 'zadavatel')->username;
                $t->resitel = $t->ref('user', 'resitel')->username;
            }
            $this->template->tasks = $task;
        } else {
            $task = $this->taskRepository->findIncompleteByOwner($this->getUser()->getId())->order(('datum DESC'));
            foreach ($task as $t) {
                $t->popis = $this->taskRepository->zkratitPopis($t->popis);
                $t->zadavatelID = 0;
                $t->zadavatelID = $t->zadavatel;
                $t->zadavatel = $t->ref('user', 'zadavatel')->username;
                $t->resitelID = 0;
                $t->resitelID = $t->resitel;
                if ($t->resitel != NULL)
                    $t->resitel = $t->ref('user', 'resitel')->username;
            }
            $this->template->tasks = $task;
        }
    }

    public function renderVybrat($todo, $id) {
        if (isset($todo) && isset($id)) {
            $this->taskRepository->vybratResitele($todo, $id);
            $this->flashMessage('Přiřazení řešitele proběhlo úspěšně!', 'success');
            $pole = $this->taskRepository->
                    findMyTasks($this->getUser()->getId())
                    ->where(array('resitel' => NULL));

            foreach ($pole as $p) {
                $p->popis = $this->taskRepository->zkratitPopis($p->popis);
                $p->zadavatelID = $p->zadavatel;
                $p->count = $this->resiteleRepository->countOfResitele($p->ID);
            }
            $this->template->tasks = $pole;
        } else {
            $pole = $this->taskRepository->
                    findMyTasks($this->getUser()->getId())
                    ->where(array('resitel' => NULL));

            foreach ($pole as $p) {
                $p->popis = $this->taskRepository->zkratitPopis($p->popis);
                $p->zadavatelID = $p->zadavatel;
                $p->count = $this->resiteleRepository->countOfResitele($p->ID);
            }
            $this->template->tasks = $pole;
        }
    }

    public function renderDone($todo) {
        $task = $this->taskRepository->findComplete($this->getUser()->getId());
        foreach ($task as $t) {
            $t->popis = $this->taskRepository->zkratitPopis($t->popis);
            $t->zadavatelID = $t->zadavatel;
            $t->zadavatel = $t->ref('user', 'zadavatel')->username;
            $t->resitelID = $t->resitel;
            if ($t->resitel != null)
                $t->resitel = $t->ref('user', 'resitel')->username;
        }
        $this->template->tasks = $task;
    }
    
    public function renderPridelit($todo) {
        if (isset($todo)) {
            $pole = $this->resiteleRepository->findAll()->where(array('ukol' => $todo));
            foreach ($pole as $p) {
                $p->ulohaID = $todo;
                $p->resitelID = $p->resitel;
                $p->name = $p->ref('user', 'resitel')->name;
                $p->podpis = $p->ref('user', 'resitel')->podpis;
                $p->rating =  $this->userRepository->getRating($p->resitel);
                $p->resitel = $p->ref('user', 'resitel')->username;
            }
            $this->template->profile = $pole;
        } else
            $this->redirect('Homepage:default');
    }

    /**
     * @return Nette\Application\UI\Form
     */
    protected function createComponentFormVybratResitele() {
        $form = new Form();

        $userPairs = $this->resiteleRepository->findAll()
                ->fetchPairs("resitel", "resitel");
        foreach ($userPairs as $user) {
            $userPairs[$user] = $this->userRepository->findNameByID($user);
        }
        $form->onSuccess[] = $this->FormVybratResiteleSubmited;
        $form->addHidden('ukol');
        $form->addSelect('resitele', 'Pro:', $userPairs)
                ->setPrompt('- Vyberte -')
                ->addRule(Form::FILLED, 'Je nutné vybrat řešitele');
        $form->addSubmit('vybrat', 'Vybrat');

        return $form;
    }

    public function actionPridatResitele($id) {
        
    }
    
    public function naplnSelectBoxVyber($taskId) {
        $userPairs = $this->resiteleRepository->findAll()
                ->where(array("ukol" => $taskId))
                ->fetchPairs("resitel", "resitel");
        foreach ($userPairs as $user) {
            $userPairs[$user] = $this->userRepository->findNameByID($user);
        }
        return $userPairs;
    }

    public function FormVybratResiteleSubmited(Form $form) {
        $this->taskRepository->vybratResitele($form->values->ukol, $form->values->resitele);
        $this->resiteleRepository->vymazatPrebytecneRezervace($form->values->ukol);
        $this->flashMessage('Přiřazení řešitele proběhlo úspěšně!', 'success');
        $this->redirect('this');
    }

    protected function createComponentNewTaskForm() {
        $form = new Form();
        $form->addText("nazev", "Název:", 40, 100)
                ->addRule(Form::FILLED, 'Je nutné zadat název úkolu.');
        $form->addText("popis", "Popis:", 40, 200)
                ->addRule(Form::FILLED, "Je nutné zadat popis úkolu");

        $form->addSubmit('create', 'Vytvořit');

        $form->onSuccess[] = $this->newTaskFormSubmitted;

        return $form;
    }

    public function newTaskFormSubmitted(Form $form) {
        $this->taskRepository->createNewTask($form->values->nazev, $form->values->popis, $this->getUser()->getId());
        $this->flashMessage('Vytváření úkolu proběhlo úspěšně!', 'success');
        $this->redirect('this');
    }

}
