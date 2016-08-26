<?php
class GoCache_Manager_Block_Adminhtml_System_Config_Form_Button_Button 

    extends Mage_Adminhtml_Block_System_Config_Form_Field implements Varien_Data_Form_Element_Renderer_Interface
{
    
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('GoCache/manager/system/config/button/button.phtml');
    }
 
    
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
 
   
    public function getAjaxUrl()
    {
        $mododeveloper = Mage::getStoreConfig('manager/config/mododeveloper');
        if($mododeveloper == 1){
            return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_enabledev/disable');
        } else {
            return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_enabledev/enable');
        }
    }
 
  
    public function getButtonHtml()
    {

        $mododeveloper = Mage::getStoreConfig('manager/config/mododeveloper');
        if($mododeveloper != 0){
            $label = "Desativar Modo de Desenvolvimento";
            $class = "save";
        } else {
            $label = "Ativar Modo de Desenvolvimento";
            $class = "delete";
        }
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'gocachemanager',
            'label'     => $this->helper('adminhtml')->__($label),
            'onclick'   => 'javascript:check(); return false;',
            'class'     => $class
        ));
 
        return $button->toHtml();
    }

  
   
}
?>