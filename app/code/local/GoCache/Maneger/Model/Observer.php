<?php
class GoCache_Maneger_Model_Observer
{
	const URL_GOCACHE = "https://api.gocache.com.br/v1/";
	
	public function GenerateToken($observer){
		$user = Mage::getStoreConfig('maneger/config/email',Mage::app()->getStore());
		$pass = Mage::helper('core')->decrypt(Mage::getStoreConfig('maneger/config/senha',Mage::app()->getStore()));
		$this->generateLog('----- Solicitando Token -----', "setaccount.log");
		$this->SetToken($user, $pass);
	}

	public function SetToken($user, $pass){
		Mage::app()->cleanCache();
		$post = array('email' => $user, 'password' => $pass);

		$url = self::URL_GOCACHE."login/";
	    $documento = 'application/x-www-form-urlencoded; charset=utf-8';

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_USERAGENT, 'Magento');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $result = curl_exec($ch);
    	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);
	    $this->generateLog('----- HttpCode do Curl Info -----', "setaccount.log");
	    $this->generateLog($httpCode, "setaccount.log");
	    $this->generateLog($result, "setaccount.log");
	    if($httpCode == '200'){
	    	$result = json_decode($result);
	    	$token = $result->response->token;
	    } else {
	    	Mage::getSingleton('adminhtml/session')->addError('Seu Email ou senha estão incorretos, por favor verifique seus dados.');
	    	return;
	    }
	    if($token){
	    	Mage::getModel('core/config')->saveConfig('maneger/config/token', $token);
	    	Mage::getModel('core/config')->saveConfig('maneger/config/validemodule', 1);
	    	$this->generateLog($token, "setaccount.log");
	    	$this->setSmartTpl($token);

	    } else {
	    	Mage::getSingleton('adminhtml/session')->addError('Seu Email ou senha estão incorretos, por favor verifique seus dados.');
	    }

	}

	public function setSmartTpl($token){

		if(Mage::getStoreConfig('maneger/config/enable_tpl',Mage::app()->getStore()) == 1){
			$url_project = Mage::getStoreConfig('maneger/config/url_project',Mage::app()->getStore());
			$url = self::URL_GOCACHE."domain/".$url_project;
			$token = Mage::getStoreConfig('maneger/config/token',Mage::app()->getStore()); 
			$header = "GoCache-Token:" . $token;
		    $documento = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
		    $post = array ("smart_tpl" => "magento", "smart_status" => "true");
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL,$url);
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
			curl_setopt($ch, CURLOPT_USERAGENT, 'Magento');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	            $header,
	            $documento
	        ));
		    $result = curl_exec($ch);
	    	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		    curl_close($ch);
		    $this->generateLog('----- HttpCode do Curl Info -----', "setaccount.log");
		    $this->generateLog($httpCode, "setaccount.log");
		    $this->generateLog($result, "setaccount.log");
		    Mage::app()->cleanCache();
		}
		
	}

	public function ClearCDNCategoryUrl($observer)
	{
		if(Mage::getStoreConfig('maneger/config/active_category',Mage::app()->getStore()) == 1){
			$category = $observer->getCategory();
			$categoryId = $category->getId();
			$allStores = Mage::app()->getStores();
			foreach ($allStores as $_eachStoreId => $val) 
				{
					$_storeId = Mage::app()->getStore($_eachStoreId)->getId();
					$_urlCategory = Mage::getSingleton('core/url_rewrite')->getResource()->getRequestPathByIdPath('category/' . $categoryId,$_storeId);
					$base_url = Mage::app()->getStore($_storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
					$clearUrl = $base_url.$_urlCategory;
					$this->generateLog('----- Cleando Url -----', "maneger.log");
					$this->generateLog($clearUrl, "maneger.log");
					$this->CurlDelete($clearUrl);
				}
		}
		return;
	}

	public function ClearCDNProductUrl($observer)
	{
		if(Mage::getStoreConfig('maneger/config/active_product',Mage::app()->getStore()) == 1){
			$product = $observer->getProduct();
			$productId = $product->getId();
			$allStores = Mage::app()->getStores();
			foreach ($allStores as $_eachStoreId => $val) 
				{
					$_storeId = Mage::app()->getStore($_eachStoreId)->getId();
					$url_product = $this->rewrittenProductUrl($productId,  $_storeId);
					$base_url = Mage::app()->getStore($_storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
					$clearUrl = $base_url.$url_product;
					$this->generateLog('----- Cleando Url -----', "maneger.log");
					$this->generateLog($clearUrl, "maneger.log");
					$this->CurlDelete($clearUrl);
				}
		}
		return;
	}
	
	public function rewrittenProductUrl($productId,  $storeId)
    {
        $coreUrl = Mage::getModel('core/url_rewrite');
        $idPath = sprintf('product/%d', $productId);
        $coreUrl->setStoreId($storeId);
        $coreUrl->loadByIdPath($idPath);
 
        return $coreUrl->getRequestPath();
    }

    public function CurlDelete($path)
	{
		
		$token = Mage::getStoreConfig('maneger/config/token',Mage::app()->getStore()); 
		
		
		$header = "GoCache-Token:" . $token;
	    $url = self::URL_GOCACHE.'cache/'.$path;
	    $documento = 'Content-Type: application/json; charset=utf-8';

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	#curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $header,
            $documento
        ));
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Magento');
	    $result = curl_exec($ch);
    	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);
	    $this->generateLog('----- HttpCode do Curl Info -----', "maneger.log");
	    $this->generateLog($httpCode, "maneger.log");
	    
	    return;
	}

	public function generateLog($variable, $name_log){
        

        if(Mage::getStoreConfig('maneger/config/active_log',Mage::app()->getStore()) == 1){
            $dir_log = Mage::getBaseDir('var').'/log/GoCache/';

            if (!file_exists($dir_log)) {
                mkdir($dir_log, 0755, true);
            }

            Mage::log($variable, null, 'GoCache/'.$name_log, true);    
        } else {
           return; 
        }
        

    }
	
}
