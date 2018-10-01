<?php

namespace CFS\User;

class CFSUser {
    private $connection = NULL;
    private $user = NULL;

    public function getUser()
    {
        $current_user = (isset($_SESSION['u_id']) && !empty($_SESSION['u_id'])) ? $_SESSION['u_id'] : NULL;

        if ($current_user) {
            $sql = "SELECT *, CONCAT_WS(' ',first_name,surname) AS name FROM cf_users WHERE user_id = :id";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue("id", $current_user);
            $stmt->execute();
            $this->user = $stmt->fetch();

            if (!empty($this->user['facebook_id'])) {
                $this->user['facebookEnabled'] = TRUE;
            }
            else {
                $this->user['facebookEnabled'] = FALSE;
            }
        }
        else {
            return NULL;
        }

        return $this->user;
    }

    public function getCountry()
    {
        if ($this->user === NULL) {
            $this->getUser();
        }

        if (isset($this->user->country) && !empty($this->user->country)) {
            return $this->user->country;
        }

        return 'GB';
    }

    public function getConnection()
    {
        if ($this->connection === NULL) {
            $config = new \Doctrine\DBAL\Configuration();
            $connectionParams = array(
                'dbname' => DB_NAME,
                'user' => DB_USER_NAME,
                'password' => DB_PASSWORD,
                'host' => DB_HOST,
                'driver' => 'pdo_mysql',
            );
            $connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

            $this->connection = $connection;
        }

        return $this->connection;
    }
}