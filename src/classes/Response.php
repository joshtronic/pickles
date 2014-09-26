<?php

class Response extends Object
{
    public $status   = 200;
    public $message  = 'OK';
    public $echo     = false;
    public $limit    = false;
    public $offset   = false;
    public $errors   = false;
    public $response = false;
    public $profiler = false;

    public function respond()
    {
        header('Content-type: application/json');

        $meta = [
            'status'  => $this->status,
            'message' => $this->message,
        ];

        foreach (['echo', 'limit', 'offset', 'errors'] as $variable)
        {
            if ($this->$variable)
            {
                $meta[$variable] = $this->$variable;
            }
        }

        $response = ['meta' => $meta];

        foreach (['response', 'profiler'] as $variable)
        {
            if ($this->$variable)
            {
                $response[$variable] = $this->$variable;
            }
        }

        $pretty = isset($_REQUEST['pretty']) ? JSON_PRETTY_PRINT : false;

        exit(json_encode($response, $pretty));
   }
}

