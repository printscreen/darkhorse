<?php

class BatchController extends Darkhorse_Controller_Action
{
    public function indexAction()
    {
        $this->view->headScript()
        ->prependFile('/js/default/batch.js', $type = 'text/javascript' );
    }

    public function viewAction()
    {
        $batches = new Model_Batches();
        $batches->getBatches(
            $this->getRequest()->getParam('customerId')
          , $this->getRequest()->getParam('active')
          , $this->getRequest()->getParam('sort')
          , $this->getRequest()->getParam('offset')
          , $this->getRequest()->getParam('limit')
        );

        $this->_helper->json(array(
            'success' => true,
            'batches' => $batches->toArray()
        ));
    }

    public function getBatchAction()
    {
        $batch = new Model_Batch(array(
            'batchId' => $this->getRequest()->getParam('batchId')
        ));
        $batch->load();
        $this->_helper->json(array(
            'batch' => $batch->toArray()
        ));
    }

    public function editAction()
    {
        $form = new Form_Batch();
        $success = false;
        $batchId = null;
        if ($form->isValid($this->getRequest()->getParams())) {
            $batch = new Model_Batch(array(
                'batchId' => $form->getElement('batchId')->getValue()
              , 'customerId' => $form->getElement('customerId')->getValue()
              , 'name' => $form->getElement('name')->getValue()
              , 'contactName' => $form->getElement('contactName')->getValue()
              , 'contactPhoneNumber' => $form->getElement('contactPhoneNumber')->getValue()
              , 'contactEmail' => $form->getElement('contactEmail')->getValue()
              , 'street' => $form->getElement('street')->getValue()
              , 'suiteApt' => $form->getElement('suiteApt')->getValue()
              , 'city' => $form->getElement('city')->getValue()
              , 'state' => $form->getElement('state')->getValue()
              , 'postalCode' => $form->getElement('postalCode')->getValue()
              , 'active' => $form->getElement('active')->getValue()
            ));
            if(is_numeric($form->getElement('batchId')->getValue())) {
                $batch->update();
            } else {
                $batch->insert();
            }
            $success = true;
            $batchId = $batch->getBatchId();
        }
        $this->_helper->json(array(
            'success' => $success,
            'batchId' => $batchId,
            'errors' => $form->getFormErrors()
        ));
    }
}
