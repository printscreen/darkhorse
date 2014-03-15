<?php
class Darkhorse_Controller_Plugin_Init extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $module = $request->getModuleName();
        $layout = Zend_Layout::getMvcInstance();
        Zend_Layout::startMvc(APPLICATION_PATH .'/modules/' .$module. '/views/layouts');
        $view = Zend_Layout::getMvcInstance()->getView();
        $view->doctype('XHTML1_STRICT');
    }
}
