<?php

class PostageController extends Darkhorse_Controller_Action
{
    public function viewAction()
    {
        $this->view->headScript()
        ->prependFile('/js/library/paginate.js', $type = 'text/javascript');

        $this->view->headScript()
        ->prependFile('/js/default/print.js', $type = 'text/javascript');
    }

    public function prepareAction()
    {
        $this->view->headScript()
        ->prependFile('/js/default/postage-prepare.js', $type = 'text/javascript' );
    }

    public function productAction()
    {
        $prepares = new Model_Prepares();
        $prepares->getProducts(
            $this->getRequest()->getParam('batchId')
        );
        $this->_helper->json(array(
            'success' => true,
            'products' => $prepares->toArray()
        ));
    }

    public function setProductTypeAction()
    {
        $form = new Form_ProductType();
        $success = false;
        if ($form->isValid($this->getRequest()->getParams())) {
            $product = new Model_Prepare(array(
                'batchId' => $form->getElement('batchId')->getValue()
              , 'shirtSex' => $form->getElement('shirtSex')->getValue()
              , 'shirtSize' => $form->getElement('shirtSize')->getValue()
              , 'shirtType' => $form->getElement('shirtType')->getValue()
            ));
            $success = $product->updateType(
                $form->getElement('oldValue')->getValue()
            );
        }
        $this->_helper->json(array(
            'success' => $success,
            'errors' => $form->getFormErrors()
        ));
    }

    public function setProductWeightAction()
    {
        $form = new Form_ProductWeight();
        $success = false;
        if ($form->isValid($this->getRequest()->getParams())) {
            $product = new Model_Prepare(array(
                'batchId' => $form->getElement('batchId')->getValue()
              , 'shirtSex' => $form->getElement('shirtSex')->getValue()
              , 'shirtSize' => $form->getElement('shirtSize')->getValue()
              , 'shirtType' => $form->getElement('shirtType')->getValue()
              , 'weight' => $form->getElement('weight')->getValue()
            ));
            $success = $product->updateWeight();
        }
        $this->_helper->json(array(
            'success' => $success,
            'errors' => $form->getFormErrors()
        ));
    }

    public function getRecipientAction()
    {
        $recipients = new Model_Recipients();
        if($this->getRequest()->getParam('cancel')) {
            $recipients->getRecipientsToCancelPostage(
                $this->getRequest()->getParam('batchId')
            );
        } else {
            $recipients->getRecipientsToPrint(
                $this->getRequest()->getParam('batchId')
              , $this->getRequest()->getParam('printtype')
            );
        }

        $this->_helper->json(array(
            'recipients' => $recipients->toArray()
        ));
    }

    public function generateStampAction()
    {
        $recipient = new Model_Recipient(array(
            'recipientId' => $this->getRequest()->getParam('recipientId')
        ));
        try {
            $recipient->generateStamp();
            $this->_helper->json(array(
                'success' => true
            ));
        } catch (Darkhorse_Endicia_Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            $this->_helper->json(array(
                'message' => $e->getMessage()
            ));
        }
    }

    public function printAction()
    {
        $recipient = new Model_Recipient(array(
            'recipientId' => $this->getRequest()->getParam('recipientId')
        ));
        $recipient->load();
        header("Content-type: image/gif");
        echo $recipient->getStamp();
        exit;
    }

    public function cancelStampAction()
    {
        $recipient = new Model_Recipient(array(
            'recipientId' => $this->getRequest()->getParam('recipientId')
        ));
        try {
            $this->_helper->json(array(
                'success' => $recipient->cancelStamp()
            ));
        } catch (Darkhorse_Endicia_Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            $this->_helper->json(array(
                'message' => $e->getMessage()
            ));
        }
    }


}