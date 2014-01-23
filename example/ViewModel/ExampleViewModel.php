<?php

namespace Intahwebz\Routing\Example\ViewModel;


use Intahwebz\ViewModel\BasicViewModel;

use Intahwebz\Jig\JigRender;
use Intahwebz\Jig\Converter\JigConverter;
use Intahwebz\Jig\JigException;

class ExampleViewModel extends BasicViewModel {


    /**
     * @var null
     */
    private $jigRenderer = null;

    private $messages = array();

    public function __construct(JigRender $jigRender) {
        $this->jigRenderer = $jigRender;
        $this->jigRenderer->bindViewModel($this);
    }

    /**
     * @throws \Exception
     */
    function render(){
        if (count($this->messages) > 0) {
            $this->setVariable('view_statusMessages', $this->messages);
        }

        if($this->template === null && $this->response === null){
            throw new \Exception("Template and response are not set - cannot display a page.");
        }

        if($this->response !== null){
            echo json_encode_object($this->response);
        }
        else{
            $this->jigRenderer->renderTemplateFile($this->template);
        }
    }
    
}

 