<?php
echo $form->create(array('action' => 'register'));
echo $form->input('name');
echo $form->input('email');
echo $form->input('username');
echo $form->input('password_confirm', array('label' => 'Password', 'type' => 'password'));
echo $form->input('password', array('label' => 'Password Confirm'));
echo $form->end('Register');
