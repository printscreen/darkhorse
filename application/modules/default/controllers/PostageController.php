<?php

class PostageController extends Darkhorse_Controller_Action
{
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
}