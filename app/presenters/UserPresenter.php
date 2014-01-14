<?php
use Nette\Application\UI\Form;
use Nette\Security as NS;

/**
 */
class UserPresenter extends BasePresenter
{
    /** @var Todo\UserRepository */
    private $userRepository;

    /** @var Todo\Authenticator */
    private $authenticator;

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
    
    public function inject(Authenticator $authenticator, Todo\UserRepository $userRepository) {
        $this->authenticator = $authenticator;
        $this->userRepository = $userRepository;
    }
    
    protected function createComponentZmenitPodpisForm() {
        $form = new Form();
        $id = $this->getUser()->getId();
        $podpis = $this->userRepository->findBy(array('ID' => $id))->fetch();
        $form->addText('podpis','Podpis:')
            ->defaultValue = $podpis->podpis;
        $form->addSubmit('odeslat',"Změnit");
        $form->onSuccess[] = $this->zmenitPodpisSubmittedForm;
        return $form;
    }
    
    public function zmenitPodpisSubmittedForm(Form $form) {
            $this->userRepository->zmenPodpis($this->getUser()->getId(), $form->values->podpis);
            $this->flashMessage('Podpis byl změněn.', 'success');
            $this->redirect('this');
    }
    
    protected function createComponentPasswordForm()
    {
        $form = new Form();
        $form->addPassword('oldPassword', 'Staré heslo:', 30)
            ->addRule(Form::FILLED, 'Je nutné zadat staré heslo.');
        $form->addPassword('newPassword', 'Nové heslo:', 30);
          //  ->addRule(Form::MIN_LENGTH, 'Nové heslo musí mít alespoň %d znaků.', 6);
        $form->addPassword('confirmPassword', 'Potvrzení hesla:', 30)
            ->addRule(Form::FILLED, 'Nové heslo je nutné zadat ještě jednou pro potvrzení.')
            ->addRule(Form::EQUAL, 'Zadná hesla se musejí shodovat.', $form['newPassword']);
        $form->addSubmit('set', 'Změnit heslo');
        $form->onSuccess[] = $this->passwordFormSubmitted;
        return $form;
    }


    public function passwordFormSubmitted(Form $form)
    {
        $values = $form->getValues();
        $user = $this->getUser();
        try {
            $this->authenticator->authenticate(array($user->getIdentity()->username, $values->oldPassword));
            $this->userRepository->setPassword($user->getId(), $values->newPassword);
            $this->flashMessage('Heslo bylo změněno.', 'success');
            $this->redirect('Homepage:');
        } catch (NS\AuthenticationException $e) {
            $form->addError('Zadané heslo není správné.');
        }
    }
}