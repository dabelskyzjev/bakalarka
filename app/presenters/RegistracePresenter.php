<?php

use Nette\Application\UI\Form;
//use Nette\Security as NS;

class RegistracePresenter extends BasePresenter {

    private $authenticator;
    private $registraceRepository;

    protected function startup() {
        parent::startup();
        $this->authenticator = $this->context->authenticator;
        $this->registraceRepository = $this->context->registraceRepository;
    }

    protected function createComponentNewRegistraceForm() {
        $form = new Form();
        $form->addText('username', 'Uživatelské jméno:', 30, 20)
                ->addRule(Form::FILLED, 'Uživatelské jméno musí být vyplněno');
        $form->addPassword('password', 'Heslo:', 30);
               // ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 3);
        // ->addRule(Form::PATTERN, 'Musí obsahovat číslici', '.*[0-9].*');
        $form->addPassword('passwordcheck', 'Ověření hesla:', 30)
                ->addRule(Form::EQUAL, 'Hesla musá být stejná', $form['password']);
        $role = array(
            0 => "zadavatel",
            1 => "programátor",
        );
        $form->addRadioList('role', 'Role:',$role);
        $form->addText('email', 'Email:', 30, 29)
                ->addRule(Form::EMAIL, 'Musí být zadána platná emailová adresa');
        $form->addSubmit('login', 'Zaregistrovat');
        $form->onSuccess[] = $this->newRegistraceFormSubmitted;
        
        return $form;
    }

    public function newRegistraceFormSubmitted(Form $form) {
        $this->registraceRepository->createPerson($form->values->username, $form->values->password, $form->values->role, $form->values->email);
        $this->flashMessage('Registrace byla úspěšná!', 'success');
        $this->redirect('Sign:in');
    }

}