<?php
/************************************************************************/
/* rndPass.class.php                                                    */
/* =====================================================================*/
/* Copyright (c) 2005 by Gobinath  (gobinathm at gmail dot com)         */
/*                                                                      */
/* Author(s): Gobinath                                                  */
/* =====================================================================*/
/* Title: Random Password Generating Class                              */
/*   Date: Feb 18th, 2005                                               */
/* =====================================================================*/
/*  Class Modification Date: April 25th, 2005                           */
/* =====================================================================*/



// Class Declaration Starts:
class rndPass{
   
	var $vals;               // For the String  
	var $PasswordLength;    // Variable To Assign Password Length
	
	function rndPass($passLen){
	
		$this->vals = "0123456789";   
		$this->PasswordLength = $passLen;
	
	}
   
	function PassGen(){
		if($this->PasswordLength > 4){ 
			$i = 0;
			$password = "";
			while (strlen($password) < $this->PasswordLength) {
				mt_getrandmax();  // Returns the maximum value that can be returned by a call rand
				$num = rand() % strlen($this->vals);
				$tmp = substr($this->vals, $num+4, 1);
				$password = $password . $tmp;
				$tmp =""; 
			}
			return $password ;  
		} else {
			echo "Oops!! Generating a Password of Less than Four character is not Secure";
			echo "\nPlease Try with More length";
		}
	}
	
}
?>