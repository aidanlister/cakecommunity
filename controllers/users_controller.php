<?php
class UsersController extends AppController
{
    var $components = array('Auth', 'Email');

    /**
     * Runs automatically before the controller action is called
     */
    function beforeFilter()
    {
        $this->Auth->allow('register', 'recover', 'verify');
        parent::beforeFilter();
    }

    /**
     * Registration page for new users
     */
    function register()
    {
        if (!empty($this->data)) {
            $this->User->create();
            if ($this->User->save($this->data)) {
                $this->Session->setFlash(__('Your account has been created.', true));
                $this->redirect('/');
            } else {
                $this->Session->setFlash(__('Your account could not be created.', true));
            }
        }
    }

    /**
     * Ran directly after the Auth component has executed
     */
    function login()
    {
        // Check for a successful login
        if (!empty($this->data) && $id = $this->Auth->user('id')) {

            // Set the lastlogin time
            $fields = array('lastlogin' => date('Y-m-d H:i:s'), 'modified' => false);
            $this->User->id = $id;
            $this->User->save($fields, false, array('lastlogin'));

            // Redirect the user
            $url = array('controller' => 'users', 'action' => 'account');
            if ($this->Session->check('Auth.redirect')) {
                $url = $this->Session->read('Auth.redirect');
            }
            $this->redirect($url);
        }
    }

    /**
     * Log a user out
     */
    function logout()
    {
       return $this->redirect($this->Auth->logout());
    }

    /**
     * Account details page (change password)
     */
    function account()
    {
        // Set User's ID in model which is needed for validation
        $this->User->id = $this->Auth->user('id');

        // Load the user (avoid populating $this->data)
        $current_user = $this->User->findById($this->User->id);
        $this->set('current_user', $current_user);

        $this->User->useValidationRules('ChangePassword');
        $this->User->validate['password_confirm']['compare']['rule'] =
            array('password_match', 'password', false);

        $this->User->set($this->data);
        if (!empty($this->data) && $this->User->validates()) {
            $password = $this->Auth->password($this->data['User']['password']);
            $this->User->saveField('password', $password);

            $this->Session->setFlash('Your password has been updated');
            $this->redirect(array('action' => 'account'));
        }
    }

    /**
     * Allows the user to email themselves a password redemption token
     */
    function recover()
    {
        if ($this->Auth->user()) {
            $this->redirect(array('controller' => 'users', 'action' => 'account'));
        }

        if (!empty($this->data['User']['email'])) {
            $Token = ClassRegistry::init('Token');
            $user = $this->User->findByEmail($this->data['User']['email']);

            if ($user === false) {
                $this->Session->setFlash('No matching user found');
                return false;
            }

            $token = $Token->generate(array('User' => $user['User']));
            $this->Session->setFlash('An email has been sent to your account, please follow the instructions in this email.');
            $this->Email->to = $user['User']['email'];
            $this->Email->subject = 'Password Recovery';
            $this->Email->from = 'Support <support@example.com>';
            $this->Email->template = 'recover';
            $this->set('user', $user);
            $this->set('token', $token);
            $this->Email->send();
        }
    }

    /**
     * Accepts a valid token and resets the users password
     */
    function verify($token_str = null)
    {
        if ($this->Auth->user()) {
            $this->redirect(array('controller' => 'users', 'action' => 'account'));
        }

        $Token = ClassRegistry::init('Token');
        
        $res = $Token->get($token_str);
        if ($res) {
            // Update the users password
            $password = $this->User->generatePassword();
            $this->User->id = $res['User']['id'];
            $this->User->saveField('password', $this->Auth->password($password));
            $this->set('success', true);

            // Send email with new password
            $this->Email->to = $res['User']['email'];
            $this->Email->subject = 'Password Changed';
            $this->Email->from = 'Support <support@example.com>';
            $this->Email->template = 'verify';
            $this->set('user', $res);
            $this->set('password', $password);
            $this->Email->send();
        }
    }
}
