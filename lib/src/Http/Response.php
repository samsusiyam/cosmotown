<?php

namespace RtRaselBD\Cosmotown\Http;

class Response
{
    private $statusCode;
    private $body;

    public function __construct($statusCode, $body = '')
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getBody()
    {
        $body = $this->body;
        return new class($body) {
            private $content;
            private $position = 0;

            public function __construct($content)
            {
                $this->content = $content;
            }

            public function getContents()
            {
                $content = $this->content;
                $this->content = '';
                return $content;
            }

            public function __toString()
            {
                return $this->content;
            }
        };
    }
}
