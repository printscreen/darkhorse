<?php

class CustomerController extends Darkhorse_Controller_Action
{
    public function indexAction()
    {
        $this->view->headScript()
        ->prependFile('/js/default/customer.js', $type = 'text/javascript' );
    }

    public function viewAction()
    {
        $customers = new Model_Customers();
        $customers->getCustomers(
            $this->getRequest()->getParam('active')
          , $this->getRequest()->getParam('sort')
          , $this->getRequest()->getParam('offset')
          , $this->getRequest()->getParam('limit')
        );

        $this->_helper->json(array(
            'success' => true,
            'customers' => $customers->toArray()
        ));
    }

    public function getCustomerAction()
    {
        $customer = new Model_Customer(array(
            'customerId' => $this->getRequest()->getParam('customerId')
        ));
        $customer->load();
        $this->_helper->json(array(
            'customer' => $customer->toArray()
        ));
    }

    public function editAction()
    {
        $form = new Form_Customer();
        $success = false;
        $customerId = null;
        if ($form->isValid($this->getRequest()->getParams())) {
            $customer = new Model_Customer(array(
                'customerId' => $form->getElement('customerId')->getValue()
              , 'name' => $form->getElement('name')->getValue()
              , 'active' => $form->getElement('active')->getValue()
            ));
            if(is_numeric($form->getElement('customerId')->getValue())) {
                $customer->update();
            } else {
                $customer->insert();
            }
            $success = true;
            $customerId = $customer->getCustomerId();
        }
        $this->_helper->json(array(
            'success' => $success,
            'customerId' => $customerId,
            'errors' => $form->getFormErrors()
        ));
    }
}
