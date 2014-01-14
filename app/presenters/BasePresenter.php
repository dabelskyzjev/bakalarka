<?php

use Nette\Application\UI\Form;



/**
 * Base presenter for all application presenters.
 *
 */

abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	/**
	 * @return Nette\Application\UI\Form
	 */

        public function handleSignOut() {
            $this->getUser()->logout();
            $this->redirect('Sign:in');
        }
        
}
