<?php

namespace CFS\International;

class CFSInternational {
    private $connection = NULL;
    private $userCountry = NULL;
    private $appCountry = NULL;
    private $nf = NULL;
    
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
    
    private function getCountryInfo($iso = 'GB') {
        $sql = "SELECT * FROM cf_countries WHERE iso = :country";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue("country", $iso);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function setAppCountry($iso = 'GB') {
        if (empty($iso)) {
            $iso = 'GB';
        }
        
        $this->appCountry = $this->getCountryInfo($iso);
    }
    
    public function getAppCountry() {
        if ($this->appCountry === NULL) {
            $this->appCountry = $this->getCountryInfo();
        }
        
        return $this->appCountry;
    }
    
    public function getUserCountry() {
        if ($this->userCountry === NULL) {
            // If set in session use that
            if (isset($_SESSION['country_iso'])) {
                $iso = $_SESSION['country_iso'];
            }
            else {
                // Else lookup from IP Address
                // This will only match countries we support, no result? Then default to GB
                $sql = "SELECT l.country_iso FROM cf_ip_lookup AS l INNER JOIN cf_countries AS c ON l.country_iso = c.iso WHERE (INET_ATON(:ip_address) BETWEEN l.starting_ip AND l.ending_ip) AND c.active = 1";
                $stmt = $this->getConnection()->prepare($sql);
                $stmt->bindValue("ip_address", $_SERVER['REMOTE_ADDR']);
                $stmt->execute();
                $iso = $stmt->fetchColumn(0);
                if (!empty($iso)) {
                    $_SESSION['country_iso'] = $iso;
                }
                else {
                    // Else default to GB
                    $iso = 'GB';
                }
            }
            
            $this->userCountry = $this->getCountryInfo($iso);
        }
        
        return $this->userCountry;
    }
    
    public function parseCountryCurrency($number = 0, $for = 'user') {
        if ($for == 'user') {
            $countryInfo = $this->getUserCountry();
        }
        else if ($for == 'app') {
            $countryInfo = $this->getAppCountry();
        }
        
        // For some reason on some systems calling parseCurrency more
        // than once returns an error and makes it unusable.
        // For now use this less robust function
        
        return (int)preg_replace("/([^0-9\\.])/i", "", $number);

        
        
        
        // Clean number of any currency symbols
        str_replace($countryInfo['currency_symbol'], '', $number);
        
        $nf = new \NumberFormatter($countryInfo['locale'], \NumberFormatter::DECIMAL);
        $new_number = $nf->parseCurrency(trim($number), $countryInfo['currency']);
        
        if (is_numeric($new_number)) {
            return $new_number;
        }
        else {
            return FALSE;
        }
    }
    
    public function formatCountryCurrency($number = 0, $for = 'user') {
        if ($for === 'user') {
            $countryInfo = $this->getUserCountry();
        }
        else if ($for === 'app') {
            $countryInfo = $this->getAppCountry();
        }
        
        setlocale(LC_MONETARY, $countryInfo['locale']);
        return $countryInfo['currency_symbol'] . money_format('%!.0i', $number) . "\n";
        
        $nf = new \NumberFormatter($countryInfo['locale'], \NumberFormatter::CURRENCY);
        return $nf->formatCurrency($number, $countryInfo['currency']);
    }
}