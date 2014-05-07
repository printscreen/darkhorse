<?php

class RecipientController extends Darkhorse_Controller_Action
{
    public function indexAction()
    {
        $this->view->headScript()
        ->prependFile('/js/library/ajax-upload.js', $type = 'text/javascript' );

        $this->view->headScript()
        ->prependFile('/js/default/recipient.js', $type = 'text/javascript' );

        $this->view->headScript()
        ->prependFile('/js/library/paginate.js', $type = 'text/javascript' );
    }

    public function uploadAction()
    {
        $success = false;
        $form = new Form_StageBatch();
        if($form->isValid($this->getRequest()->getParams())) {
            $recipients = new Model_Recipients();
            $recipients->massInsertFromCsv(
                $_FILES['file']['tmp_name']
              , $form->getElement('batchId')->getValue()
            );
            unlink($_FILES['file']['tmp_name']);
            $success = true;
        }
        $this->_helper->json(array(
            'success' => $success,
            'errors' => $form->getFormErrors()
        ));
    }

    public function viewAction()
    {
        $recipients = new Model_Recipients();
        $recipients->getRecipients(
            $this->getRequest()->getParam('batchId')
          , $this->getRequest()->getParam('searchField')
          , $this->getRequest()->getParam('searchText')
          , $this->getRequest()->getParam('sort')
          , $this->getRequest()->getParam('offset')
          , $this->getRequest()->getParam('limit')
        );

        $this->_helper->json(array(
            'success' => true,
            'recipients' => $recipients->toArray()
        ));
    }

    public function getRecipientAction()
    {
        $recipient = new Model_Recipient(array(
            'recipientId' => $this->getRequest()->getParam('recipientId')
        ));
        $recipient->load();
        $this->_helper->json(array(
            'success' => true,
            'recipient' => $recipient->toArray()
        ));
    }

    public function editAction()
    {
        $form = new Form_Recipient();
        $success = false;
        $recipientId = null;
        if ($form->isValid($this->getRequest()->getParams())) {
            $recipient = new Model_Recipient(array(
                'recipientId' => $form->getElement('recipientId')->getValue()
              , 'batchId' => $form->getElement('batchId')->getValue()
              , 'email' => $form->getElement('email')->getValue()
              , 'firstName' => $form->getElement('firstName')->getValue()
              , 'lastName' => $form->getElement('lastName')->getValue()
              , 'addressLineOne' => $form->getElement('addressLineOne')->getValue()
              , 'addressLineTwo' => $form->getElement('addressLineTwo')->getValue()
              , 'city' => $form->getElement('city')->getValue()
              , 'state' => $form->getElement('state')->getValue()
              , 'postalCode' => $form->getElement('postalCode')->getValue()
              , 'shirtSex' => $form->getElement('shirtSex')->getValue()
              , 'shirtSize' => $form->getElement('shirtSize')->getValue()
              , 'shirtType' => $form->getElement('shirtType')->getValue()
              , 'quantity' => $form->getElement('quantity')->getValue()
            ));
            if(is_numeric($form->getElement('recipientId')->getValue())) {
                $recipient->update();
            } else {
                $recipient->insert();
            }
            $success = true;
            $recipientId = $recipient->getRecipientId();
        }
        $this->_helper->json(array(
            'success' => $success,
            'recipientId' => $recipientId,
            'errors' => $form->getFormErrors()
        ));
    }

    public function moveAction()
    {
        $form = new Form_MoveRecipient();
        $success = false;
        if ($form->isValid($this->getRequest()->getParams())) {
            $recipients = new Model_Recipients();
            $success = $recipients->moveRecipients(
                $form->getElement('fromBatchId')->getValue()
              , $form->getElement('toBatchId')->getValue()
              , $form->getElement('who')->getValue()
              , $form->getElement('recipientIds')->getValue()
            );
        }

        $this->_helper->json(array(
            'success' => $success,
            'errors' => $form->getFormErrors()
        ));
    }

    public function statsAction()
    {
        $stats = new Model_Stats();
        $fileName = sprintf('stats-%s-%s.csv'
          , date('Y-m-d')
          , $this->getRequest()->getParam('name', 'no-name')
        );
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$fileName}");
        $fh = fopen('php://output', 'w');
        $stats->export($fh, $this->getRequest()->getParam('batchId'));
        fclose($fh);
    }

    public function exportAction()
    {
        $recipients = new Model_Recipients();
        $fileName = sprintf('export-%s-%s.csv'
          , date('Y-m-d')
          , $this->getRequest()->getParam('name', 'no-name')
        );
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$fileName}");
        $fh = fopen('php://output', 'w');
        $recipients->export($fh, $this->getRequest()->getParam('batchId'));
        fclose($fh);
    }

    public function verifyAction()
    {
        $cache = Zend_Registry::get(CACHE);
        $batchKey = $this->getRequest()->getParam('batchId') . '_batch_verify';
        /**
        if($session->$batchKey) {
            $this->_helper->json(array(
                'success' => false,
                'message' => 'Verification in process for this batch'
            ));
        }
        */
        $cache->save('0|0', $batchKey);
        try {
            session_write_close();
            set_time_limit(0);
            $recipients = new Model_Recipients();
            $recipients->verifyRecipients(
                $this->getRequest()->getParam('batchId')
              , $batchKey
            );
        } catch (Exception $e) {
            $this->_helper->json(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
        $this->_helper->json(array(
            'success' => true
        ));
    }

    public function verifyStatusAction()
    {
        $cache = Zend_Registry::get(CACHE);
        $batchKey = $this->getRequest()->getParam('batchId') . '_batch_verify';
        $this->_helper->json(array(
            'status' => $cache->load($batchKey)
        ));
    }
}
