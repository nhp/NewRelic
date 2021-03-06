<?php

/**
 * New Relic General API model
 * 
 * @category   ProxiBlue
 * @package    ProxiBlue_NewRelic
 * @author     Lucas van Staden (support@proxiblue.com.au)
 **/

class ProxiBlue_NewRelic_Model_Api extends ProxiBlue_NewRelic_Model_Abstract {
    
    protected $_eventType = 'Api';
    
    /**
     * Get the application names
     * 
     * @return array
     */
    public function getApplications(){
        $result = $this->talkToNewRelic('applications');
        // parse the xml
        $xmlObj = simplexml_load_string($result);
        $applications = array();
        foreach ($xmlObj->application as $key => $app) {
            $applications[] = (string)$app->name;
        }
        //save the applications to the system for list lookup in admin
        $config = new Mage_Core_Model_Config();
        $config ->saveConfig('newrelic/appnames/lookup/', serialize($applications), 'default', 0);
        return $applications;
        
    }
    
    /**
     * Get the New Relic Account Details
     * @return array
     */
    public function getAccountDetails(){
        $headers = array(
            'x-api-key:' . $this->_api_key
        );
        $http = new Varien_Http_Adapter_Curl();
        
        $http->write('GET', 'https://api.newrelic.com/api/v1/accounts.xml', '1.1', $headers);
        $response = $http->read();
        $response = Zend_Http_Response::extractBody($response);
        // parse the xml
        $xmlObj = simplexml_load_string($response);
        $key = 'data-access-key';
        $accountDetails = array('accountid'=>(string)$xmlObj->account->id,'accesskey'=>(string)$xmlObj->account->$key);
        $config = new Mage_Core_Model_Config();
        $config ->saveConfig('newrelic/api/account_id', $accountDetails['accountid'], 'default', 0);
        $config ->saveConfig('newrelic/api/data_access_key', $accountDetails['accesskey'], 'default', 0);
        return $accountDetails;
        
    }
    
    /**
     * Talk to new relic
     * 
     * @param string $restPoint
     * @return string
     */
    public function talkToNewRelic($restPoint) {
        $headers = array(
            'x-api-key:' . $this->_api_key
        );
        $http = new Varien_Http_Adapter_Curl();
        
        $http->write('GET', 'https://api.newrelic.com/api/v1/accounts/'.$this->_account_Id.'/'.$restPoint.'.xml', '1.1', $headers);
        $response = $http->read();
        $response = Zend_Http_Response::extractBody($response);
        return $response;
    }
    
}

?>
