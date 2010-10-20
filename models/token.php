<?php
class Token extends AppModel
{
    /**
     * Create a new ticket by providing the data to be stored in the ticket.
     */
    function generate($data = null)
    {
        $data = array(
          'token' => substr(md5(uniqid(rand(), 1)), 0, 10),
          'data'  => serialize($data),
        );

        if ($this->save($data)) {
            return $data['token'];
        }

        return false;
    }

    /**
     * Return the value stored or false if the ticket can not be found.
     */
    function get($token)
    {
        $this->garbage();
        $token = $this->findByToken($token);
        if ($token) {
          $this->delete($token['Token']['id']);
          return unserialize($token['Token']['data']);
        }

        return false;
    }

    /**
     * Remove old tickets
     */
    function garbage()
    {
        return $this->deleteAll(array('created < INTERVAL -1 DAY + NOW()'));
    }
}
