<?php

class GoCache_Maneger_Adminhtml_EnabledevController extends Mage_Adminhtml_Controller_Action
{

    const URL_GOCACHE = "https://api.gocache.com.br/v1/";

    public function enableAction()
    {
        $url_project = Mage::getStoreConfig('maneger/config/url_project',Mage::app()->getStore());
        $url_project = str_replace( 'http://', '', $url_project);
        $url = self::URL_GOCACHE."domain/".$url_project;
        $token = Mage::getStoreConfig('maneger/config/token',Mage::app()->getStore()); 
        $header = "GoCache-Token:" . $token;
        $documento = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
        $post = array ("deploy_mode" => "true");
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
        $this->generateLog($url, "setaccount.log");
        $this->generateLog($result, "setaccount.log");
        
        if($httpCode == 200){
            Mage::getSingleton('adminhtml/session')->addSuccess('Você está em modo de desenvolvimento, não iremos servir mais conteúdo com o CDN da GoCache.');
            Mage::getModel('core/config')->saveConfig('maneger/config/mododeveloper', 1);
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Não foi possível ativar o modo de desenvolvimento, verifique se a url do projeto está correta.');
            Mage::getModel('core/config')->saveConfig('maneger/config/mododeveloper', 0);
        }
        Mage::app()->cleanCache();
         
        
    }

    public function disableAction()
    {
        $url_project = Mage::getStoreConfig('maneger/config/url_project',Mage::app()->getStore());
        $url_project = str_replace( 'http://', '', $url_project);
        $url = self::URL_GOCACHE."domain/".$url_project;
        $token = Mage::getStoreConfig('maneger/config/token',Mage::app()->getStore()); 
        $header = "GoCache-Token:" . $token;
        $documento = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
        $post = array ("deploy_mode" => "false");
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
        $this->generateLog($url, "setaccount.log");
        $this->generateLog($httpCode, "setaccount.log");
        $this->generateLog($result, "setaccount.log");
        
        if($httpCode == 200){
            Mage::getSingleton('adminhtml/session')->addSuccess('O modo de desenvolvimento foi desativado. O CDN já está trabalhando corretamente.');
            Mage::getModel('core/config')->saveConfig('maneger/config/mododeveloper', 0);
        } else {
            Mage::getModel('core/config')->saveConfig('maneger/config/mododeveloper', 1);
            Mage::getSingleton('adminhtml/session')->addError('Não foi possível ativar desativar o modo de desenvolvimento, verifique se a url do projeto está correta');
        }
        Mage::app()->cleanCache();
        
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

?>