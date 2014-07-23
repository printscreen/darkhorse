<?php

class ScanFormController extends Darkhorse_Controller_Action
{
    public function indexAction()
    {
        $this->view->headScript()
        ->prependFile('/js/library/paginate.js', $type = 'text/javascript');

        $this->view->headScript()
        ->prependFile('/js/default/scan-form.js', $type = 'text/javascript');
    }

    public function viewAction()
    {
        $scanForms = new Model_ScanForms();
        $scanForms->getScanForms(
            $this->getRequest()->getParam('batchId')
          , $this->getRequest()->getParam('sort')
          , $this->getRequest()->getParam('offset')
          , $this->getRequest()->getParam('limit')
        );

        $this->_helper->json(array(
            'success' => true,
            'scanForms' => $scanForms->toArray()
        ));
    }

    public function editAction()
    {
        $form = new Form_ScanForm();
        $success = false;
        if ($form->isValid($this->getRequest()->getParams())) {
            $scanForm = new Model_ScanForm(array(
                'scanFormId' => $form->getElement('scanFormId')->getValue()
              , 'batchId' => $form->getElement('batchId')->getValue()
              , 'name' => $form->getElement('name')->getValue()
            ));
            if(is_numeric($form->getElement('scanFormId')->getValue())) {
                $scanForm->update();
            } else {
                $scanForm->setIsGenerated(false);
                $scanForm->insert();
            }
            $success = true;
        }
        $this->_helper->json(array(
            'success' => $success,
            'errors' => $form->getFormErrors()
        ));
    }

    public function printAction()
    {

    }

    public function getRecipientAction()
    {
        $scanFormId = $this->getRequest()->getParam('scanFormId');
        $scanForm = new Model_Recipients();

        $scanForm->getScanFormAvailableRecipients($scanFormId);
        $availableRecipients = $scanForm->toArray();

        $scanForm->getScanFormRecipients($scanFormId);
        $recipients = $scanForm->toArray();

        $this->_helper->json(array(
            'success' => true
          , 'availableRecipients' => $availableRecipients
          , 'recipients' => $recipients
        ));
    }

    public function editRecipientAction()
    {
        $moo = $this->getRequest()->getParam('add');
        $recipients = new Model_Recipients();
        $success = $recipients->setScanFormRecipients(
            $this->getRequest()->getParam('scanFormId')
          , $this->getRequest()->getParam('add')
          , $this->getRequest()->getParam('remove')
        );
        $this->_helper->json(array(
            'success' => $success
        ));
    }

    public function generateFormAction()
    {
        $scanForm = new Model_ScanForm(array(
            'scanFormId' => $this->getRequest()->getParam('scanFormId')
        ));
        $success = $scanForm->generateScanForm();
        $this->_helper->json(array('success' => true));
    }
}
