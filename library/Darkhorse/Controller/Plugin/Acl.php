<?php
class Darkhorse_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    protected $_publicModules;
    protected $_publicControllers;
    protected $_publicActions;

    public function __construct()
    {
        $this->_publicModules = array();
        $this->_publicControllers = array('default:error');
        $this->_publicActions = array(
                  'default:auth:login'
                , 'default:auth:forgot-password'
                , 'default:auth:reset-password'
                , 'default:auth:logout'
                , 'default:error:error'
                , 'default:index:index'
                );
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        //If not dispatchable
        if(!(Zend_Controller_Front::getInstance()->getDispatcher()->isDispatchable($request))) {
            return false;
        }

        $reqModule = $this->getRequest()->getModuleName();
        $reqController = $this->getRequest()->getControllerName();
        $reqAction = $this->getRequest()->getActionName();
        $reqModuleStr = $reqModule;
        $reqControllerStr = $reqModule.':'.$reqController;
        $reqActionStr = $reqModule.':'.$reqController.':'.$reqAction;

        if( in_array($reqControllerStr, $this->_publicControllers) ||
            in_array($reqActionStr, $this->_publicActions)) {
            //If module, controller, or action is publically open, don't run it through ACL
            return;
        }

        //TODO
        //write acl
    }
}
