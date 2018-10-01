<?php

namespace CFS\ChurchDirectory;

class CFSChurchDirectory {
    private $connection = NULL;
    private $country = NULL;
    
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
    
    public function setCountry($country) {
        $this->country = $country;
    }
    
    protected function getCountry() {
        if (empty($this->country)) {
            $this->country = 'GB';
        }
        
        return $this->country;
    }
    
    public function getChurches() {
        $sql = "SELECT church_id, street, church_name, church_url FROM cf_church_directory WHERE country = :country";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue("country", $this->getCountry());
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getRegions() {
        $sql = "SELECT region AS name, count(*) AS churches FROM cf_church_directory WHERE country = :country GROUP BY region";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue("country", $this->getCountry());
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getChurchesForRegion($region = NULL) {
        $sql = "SELECT church_id, latitude, longitude, street, area, region, country, postal_code, church_name, church_url FROM cf_church_directory WHERE country = :country AND region = :region";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue("country", $this->getCountry());
        $stmt->bindValue("region", $region);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getNumberOfChurches() {
        $sql = "SELECT count(*) AS churches FROM cf_church_directory";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn(0);
    }
    
    public function getDefaultRegionForCountry() {
        $sql = "SELECT default_region AS region FROM cf_countries WHERE iso = :country";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue("country", $this->getCountry());
        $stmt->execute();
        return $stmt->fetchColumn(0);
    }
    
    public function getChurchInfo($id) {
        $sql = "SELECT church_id, latitude, longitude, street, area, region, country, postal_code, church_name, church_url FROM cf_church_directory WHERE church_id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue("id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }
}