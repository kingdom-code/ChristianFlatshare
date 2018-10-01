<?php

namespace CFS\Mailer;

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Mandrill;

class CFSMailer {
    private $mandrill = NULL;
    
    public function __construct() {
        $mandrill = new Mandrill(EMAIL_MANDRILL_API_KEY);
        $this->mandrill = $mandrill;
    }
    
    public function testBody($body) {
        // Inline CSS Styles
        $css = file_get_contents(__DIR__ . '/../../../web/css/email.css');
        
        $CSSInliner = new CssToInlineStyles($body, $css);
        return $CSSInliner->convert();
    }
    
    public function createMessage($subject, $body, $to, $bcc = NULL, $from = NULL) {
        // Inline CSS Styles
        $css = file_get_contents(__DIR__ . '/../../../web/css/email.css');
        
        $CSSInliner = new CssToInlineStyles($body, $css);
        $body = $CSSInliner->convert();
        
        $message = array(
            'subject' => $subject,
            'from_email' => 'info@christianflatshare.org',
            'from_name' => 'Christian Flatshare',
            'headers' => array('Reply-To' => 'no-reply@christianflatshare.org'),
            'auto_text' => TRUE,
            'inline_css' => FALSE,
            'html' => $body
        );
        
        if ($from != NULL) {
          $message['headers'] = array('Reply-To' => $from);
        }
        
        if (is_array($to)) {
            $message['to'] = array();
            
            foreach ($to as $email) {
                $message['to'][] = array('email' => $email, 'name' => NULL);
            }
        }
        else {
            $message['to'] = array(
                array('email' => $to, 'name' => NULL),
            );
        }
        
        // Add optional BCC
        if ($bcc !== NULL) {
            $message['bcc_address'] = $bcc;
        }
        
        return $message;
    }
    
    public function sendMessage($msg, $force = false) {
        if (SERVER_ENVIRONMENT == 'PRODUCTION' || $force === true) {
            $result = $this->mandrill->messages->send($msg);
        }
        else {
            return 1;
        }
        
        if($result[0]['status'] == 'sent') {
            return 1;
        }
        else {
            return 0;
        }
    }
    
    
    // Depreciated
    public function getFailedRecipients() {
        return NULL;
    }
}
