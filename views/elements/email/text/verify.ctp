Dear <?php echo $user['User']['name']; ?>,

Your password has been reset, please use the following
details to log into our site.

    Username: <?php echo $user['User']['username']; ?>

    Password: <?php echo $password; ?>

Please change your password to something more memorable.
You can log in to change your password at this address:

    <?php echo Router::url(array('controller' => 'users', 'action' => 'login'), true); ?>

Thanks,
Support