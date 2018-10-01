<?php
namespace CFS\Facebook;

use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;

class CFSFacebook {
    private $connection = NULL;
    private $helper = NULL;

    public function __construct()
    {
        FacebookSession::setDefaultApplication('241207662692677', '023071dc451fc3322181435002811ea6');
        $this->helper = new FacebookRedirectLoginHelper('http://' . $_SERVER["SERVER_NAME"] . '/fb-login.php');
    }

    public function getLoginURL()
    {
        return $this->helper->getLoginUrl();
    }

    public function getSessionFromRedirect($currentUser)
    {
				error_log(__FILE__.' XXXTEST',0);
        try {
            $session = $this->helper->getSessionFromRedirect();
        } catch(FacebookRequestException $ex) {
            // When Facebook returns an error
					  error_log(__FILE__.' XXTEST1',0);
            print 'Facebook Error'; exit;
        } catch(\Exception $ex) {
            // When validation fails or other local issues
				    error_log(__FILE__.' XXTEST2',0);
            print 'Validation Failed'; exit;
        }
				error_log(__FILE__. 'XXTEST3',0);
        if ($session) {
            // Logged in.
				
					  error_log(__FILE__.' CURRENTUSER '.print_r( $currentUser, true ) );
					  error_log(__FILE__.' CURRENT_USER '.print_r( $current_user, true ) );
					  error_log(__FILE__.' CURRENT_USER '.$current_user, true  );
					  error_log(__FILE__.' SESSION '.print_r( $session, true ) );
            $user = $this->getUserFromFacebook($currentUser, $session);

            $_SESSION['u_id'] = $user['user_id'];
            $_SESSION['u_name'] = $user['name'];
            $_SESSION['u_email'] = $user['email_address'];
            $_SESSION['u_access'] = $user['access'];
            $_SESSION['show_hidden_ads'] = 'no';
            $_SESSION['fb_access_token'] = $session->getToken();

            // Redirect according to user priviledges
            if ($_SESSION['u_access'] == 'member') {
                header("Location: your-account-manage-posts.php");
            } else if ($_SESSION['u_access'] == 'advertiser') {
                header("Location: advertisers.php");
            } else {
                header("Location: administrator/index.php");
            }
        }
    }

    public function getLogoutURL()
    {
        return $this->helper->getLogoutUrl($session, 'http://' . $_SERVER["SERVER_NAME"] . '/fb-logout.php');
    }

    public function logout()
    {
        unset($_SESSION['fb_access_token']);
    }

    public function getUserFromFacebook($currentUser, $session)
    {
				error_log('gerUserFromFacebook currentUser '.print_r( $currentUser, true ) );
				error_log('gerUserFromFacebook session '.print_r( $session, true ) );
				error_log('BEFORE CALL ================ user='.print_r( $user, true ) );
        try {
            $user = (new FacebookRequest(
                $session, 'GET', '/me'
            ))->execute()->getGraphObject(GraphUser::className());
            // print "Name: " . $user_profile->getName();
        } catch(FacebookRequestException $e) {
            return FALSE;
            print "Exception occured, code: " . $e->getCode() . " with message: " . $e->getMessage(); exit;
        } catch (\Exception $e) {
            return FALSE;
            print "Exception occured"; exit;
        }

				error_log('AFTER CALL ================ user='.print_r( $user, true ) );
				error_log('Email attempt = '.$user->getProperty("email"));
				error_log('gerUserFromFacebook userr '.print_r( $user, true ) );
        if ($user) {
            $result = $this->getUserDetails($user);

            // No user found with that Facebook ID
            if ($result === FALSE) {
                if ($currentUser) {
                    // Associate the currently logged in user with this Facebook ID
                    $sql = "UPDATE cf_users SET facebook_id = :id WHERE user_id = :user";
                    $stmt = $this->getConnection()->prepare($sql);
                    $stmt->bindValue("id", $user->getId());
                    $stmt->bindValue("user", $currentUser);
                    $stmt->execute();

                    $result = $this->getUserDetails($user);
                }
                else {
                    // Create a new user with their Facebook Profile details
                    $result = $this->createUserFromFacebook($user);
                }
            // User has been found, udpate last_login
            } else {
                 //$sql = "UPDATE cf_users SET last_login = now() WHERE user_id = 32670";
                 $sql = "UPDATE cf_users SET last_login = now() WHERE user_id = :user";
                 $stmt = $this->getConnection()->prepare($sql);
                 $stmt->bindValue("user", $result['user_id']);
                 $stmt->execute();
            }
        }

        return $result;
    }

    private function getUserDetails(GraphUser $user)
    {
        $sql = "SELECT *, CONCAT_WS(' ',first_name,surname) AS name FROM cf_users WHERE facebook_id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue("id", $user->getId());
        $stmt->execute();
        return $stmt->fetch();
    }

    private function createUserFromFacebook(GraphUser $user)
    {
			  error_log('CUFF user '.print_r( $user, true ) );

        $sql = "SELECT user_id FROM cf_users WHERE email_address = :email";
        $existingUser = $this->getConnection()->prepare($sql);
        $existingUser->bindValue("email", $user->getEmail());
        $existingUser->execute();
        $user_id = $existingUser->fetchColumn(0);

        if ($user_id) {
          $sql = "UPDATE cf_users SET
              facebook_id = :id,
              last_login = now(),
              first_name = :first_name,
              surname = :last_name,
              gender = :gender,
              active = 1,
              email_verified = 1
          WHERE user_id = :user_id";
          $updateCurrentAccount = $this->getConnection()->prepare($sql);
          $updateCurrentAccount->bindValue("id", $user->getId());
          $updateCurrentAccount->bindValue("first_name", $user->getFirstName());
          $updateCurrentAccount->bindValue("last_name", $user->getLastName());
          $updateCurrentAccount->bindValue("gender", substr($user->getGender(), 0, 1));
          $updateCurrentAccount->bindValue("user_id", $user_id);
          $updateCurrentAccount->execute();
        }
        else {
          $sql = "INSERT INTO cf_users SET
              facebook_id = :id,
              created_date = now(),
              last_updated_date = now(),
              last_login = now(),
              first_name = :first_name,
              surname = :last_name,
              email_address = :email,
              gender = :gender,
              active = 1,
              email_verified = 1,
              hear_about = 'Facebook',
              access = 'member'";
          $createNewAccount = $this->getConnection()->prepare($sql);
          $createNewAccount->bindValue("id", $user->getId());
          $createNewAccount->bindValue("first_name", $user->getFirstName());
          $createNewAccount->bindValue("last_name", $user->getLastName());
          $createNewAccount->bindValue("email", $user->getEmail());
          $createNewAccount->bindValue("gender", substr($user->getGender(), 0, 1));
          error_log('INSERT user - ID '.print_r( $user->getId(), true ) );
          error_log('INSERT user - email  '.print_r( $user->getEmail(), true ) );
          error_log('INSERT user - first '.print_r( $user->getFirstName(), true ) );
          error_log('INSERT user - last '.print_r( $user->getLastName(), true ) );
          $createNewAccount->execute();
        }

        return $this->getUserDetails($user);
    }

    public function getMutualFriends($user_one, $user_two)
    {
        if ($user_one == $user_two) return array();
        $sql = "SELECT u.* FROM cf_fb_friends AS o, cf_fb_friends AS l
            INNER JOIN cf_fb_users AS u ON l.facebook_id = u.facebook_id
            WHERE o.facebook_id = l.facebook_id AND l.user_id = :user_one AND o.user_id = :user_two ORDER BY RAND() LIMIT 0,16";
        $findMutualFriends = $this->getConnection()->prepare($sql);
        $findMutualFriends->bindValue("user_one", $user_one);
        $findMutualFriends->bindValue("user_two", $user_two);
        $findMutualFriends->execute();
        return $findMutualFriends->fetchAll();
    }

    public function importFriends($user, $url = NULL)
    {
        // Do we have a User?
        if (!isset($user) || empty($user) || !is_array($user)) {
            return FALSE;
        }

        // Don't reimport friends within a 7 day period
        $now = new \DateTime(NULL, new \DateTimeZone('UTC'));
        $seven_days_ago = $now->sub(new \DateInterval('P7D'));
        if ($url === NULL && ($user['fb_friends_last_refreshed'] <= $seven_days_ago && $user['fb_friends_last_refreshed'] != NULL)) {
            return FALSE;
        }

        // Do we have a Facebook Access Token?
        if (!isset($_SESSION['fb_access_token']) || empty($_SESSION['fb_access_token'])) {
            return FALSE;
        }
        else {
            $session = new FacebookSession($_SESSION['fb_access_token']);
        }

        // Make correct request to Facebook, we could be paging so use URL if given
        try {
            if ($url === NULL) {
                $url = '/me/friends?fields=picture.height(40).width(40),id,first_name,last_name';
            }
            else {
                $url = str_replace('https://graph.facebook.com', '', $url);
            }

            $friends = (new FacebookRequest(
                $session, 'GET', $url
            ))->execute()->getGraphObject();
        } catch(FacebookRequestException $e) {
            return FALSE;
        } catch (\Exception $e) {
            return FALSE;
        }

        $friendObjects = $friends->getPropertyAsArray('data');

        // If Facebook has returned results cache them in the database
        if (is_array($friendObjects) && !empty($friendObjects)) {
            $sql = "INSERT INTO cf_fb_users SET
                facebook_id = :id,
                first_name = :first_name,
                last_name = :last_name,
                picture_url = :picture,
                last_updated = now()
            ON DUPLICATE KEY UPDATE
                first_name = :first_name,
                last_name = :last_name,
                picture_url = :picture,
                last_updated = now()";
            $addFBUser = $this->getConnection()->prepare($sql);

            $sql = "INSERT INTO cf_fb_friends SET
                user_id = :user,
                facebook_id = :id
            ON DUPLICATE KEY UPDATE
                user_id = :user";
            $addRelationship = $this->getConnection()->prepare($sql);

            foreach ($friendObjects as $friend) {
                $friend = $friend->cast(GraphUser::className());

                // Insert facebook user
                $addFBUser->bindValue("id", $friend->getId());
                $addFBUser->bindValue("first_name", $friend->getFirstName());
                $addFBUser->bindValue("last_name", $friend->getLastName());
                $addFBUser->bindValue("picture", $friend->getProperty('picture')->getProperty('url'));
                $addFBUser->execute();
                
                // Insert relationship
                $addRelationship->bindValue("user", $user['user_id']);
                $addRelationship->bindValue("id", $friend->getId());
                $addRelationship->execute();
            }

            // Update last refreshed
            $sql = "UPDATE cf_users SET fb_friends_last_refreshed = :now WHERE user_id = :user";
            $updateLastRefreshed = $this->getConnection()->prepare($sql);
            $updateLastRefreshed->bindValue("now", $now, "datetime");
            $updateLastRefreshed->bindValue("user", $user['user_id']);
            $updateLastRefreshed->execute();

            // Go to next page
            $this->importFriends($user, $friends->getProperty('paging')->getProperty('next'));
        }

        return TRUE;
    }

    private function getConnection()
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
