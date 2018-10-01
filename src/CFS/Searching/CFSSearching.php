<?php

namespace CFS\Searching;

require_once 'vendor/autoload.php';

class CFSSearching {
    private $connection = NULL;
    
    private function getConnection() {
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
    
    public function getCountryListings($type = 'offered', $level = 'region', $admin = NULL, $country = 'GB') {
        switch($level) {
            case 'region':
                if ($type == "offered") {
                    $sql = "SELECT region, count(*) AS num_ads FROM cf_offered AS o WHERE expiry_date >= now() AND suspended = 0 AND published = 1 AND country = :country GROUP BY region LIMIT 0,10";
                }
                else {
                    $sql = "SELECT region, count(*) AS num_ads FROM cf_wanted AS o WHERE expiry_date >= now() AND suspended = 0 AND published = 1 AND country = :country GROUP BY region LIMIT 0,10";
                }
                
                $stmt = $this->getConnection()->prepare($sql);
                $stmt->bindValue("country", $country);
                break;
            case 'area':
                if ($type == "offered") {
                    $sql = "SELECT area, count(*) AS num_ads FROM cf_offered AS o WHERE expiry_date >= now() AND suspended = 0 AND published = 1 AND country = :country AND region = :region GROUP BY area LIMIT 0,10";
                }
                else {
                    $sql = "SELECT area, count(*) AS num_ads FROM cf_wanted AS o WHERE expiry_date >= now() AND suspended = 0 AND published = 1 AND country = :country AND region = :region GROUP BY area LIMIT 0,10";
                }
                
                $stmt = $this->getConnection()->prepare($sql);
                $stmt->bindValue("country", $country);
                $stmt->bindValue("region", $admin);
                break;
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
