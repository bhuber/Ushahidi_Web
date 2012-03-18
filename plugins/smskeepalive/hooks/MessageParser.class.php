<?php
    class MessageParser
    {
        /*
        A parser for  messages 
        messages are ordered strings
        message type tokens  
        */

        //Message type definitions

        CONST TYPE_LOCATION     ="location";
        CONST TYPE_CHECK_IN     ="checkin";
        CONST TYPE_CHECKOUT     ="checkout";
        CONST TYPE_HELP         ="help";
        CONST TYPE_ALL_CLEAR    ="all_clear";
        CONST TYPE_STATUS       ="status";

        //Message type tokens
        CONST TOKENS_TYPE_LOCATION    = "lo,loc,location";
        CONST TOKENS_TYPE_CHECKIN     = "ci,check,checkin,in";
        CONST TOKENS_TYPE_CHECKOUT    = "co,checkout,out";
        CONST TOKENS_TYPE_HELP        = "lp,help,sos,911";
        CONST TOKENS_TYPE_ALL_CLEAR   = "ac,clear,safe";
        CONST TOKENS_TYPE_STATUS      = "status,st,update";

        CONST REGEX_IDENTITY          = '/^[a-zA-Z][0-9]{5}$/';

        CONST ERROR_MISSING_ELEMENTS  ="Missing elements in message: ";
        CONST ERROR_INVALID_MESSAGE   ="Message is invalid - type not recognized";


        private $raw_message;
        private $message_type;
        private $identifier;
        private $location;
        private $password;
        private $message;

        public function getRawMessage()
        {
            return $this->raw_message;
        }

        public function getMessageType()
        {
            return $this->message_type;
        }

        public function getIdentifier()
        {
            return $this->identifier;
        }

        public function getLocation()
        {
            return $this->location;
        }

        public function getPassword()
        {
            return $this->password;
        }                

        public function getMessage()
        {
            return $this->message;
        }



        public function __construct($message, $camefrom, $wentto){
            $elements = explode(" ", $message);
            $this->message_type=self::find_message_type($elements[0]);
            if (!$this->message_type){
                throw new Exception(self::ERROR_INVALID_MESSAGE);
            }

            switch ($this->message_type)
            {
                case (self::TYPE_CHECK_IN):
                    if ($elements[1] && preg_match(self::REGEX_IDENTITY, $elements[1]))
                    { 
                        $this->identifier=$elements[1];
                        $locationStartIndex = 2;
                    }
                    else
                    {
                        $locationStartIndex=1;
                    }
                    for ($i = $locationStartIndex; $i < count($elements); $i++){
                        $this->location .= $elements[$i]." ";
                    }
                    $this->location=trim($this->location);
                    break;
                case (self::TYPE_CHECKOUT):
                    //Miniumum number of elements is 2
                    if (count($elements)<2 ) throw new Exception(self::ERROR_MISSING_ELEMENTS);
                    if ($elements[1] && preg_match(self::REGEX_IDENTITY, $elements[1]))
                    { 
                        $this->identifier=$elements[1];
                        $locationStartIndex = 2;
                    }
                    else
                    {
                        $locationStartIndex=1;
                    }
                    $passwordIndex=count($elements)-1;
                    $this->password=$elements[$passwordIndex];
                    for ($i=$locationStartIndex; $i<$passwordIndex; $i++)
                    {
                        $this->location.=$elements[$i]." ";
                    }
                    $this->location=trim($this->location);
                    break;
                case (self::TYPE_LOCATION):
                    if ($elements[1] && preg_match(self::REGEX_IDENTITY, $elements[1]))
                    { 
                        $this->identifier=$elements[1];
                        $locationStartIndex = 2;
                    }
                    else
                    {
                        $locationStartIndex=1;
                    }
                    for ($i = $locationStartIndex; $i<count($elements); $i++){
                        $this->location.=$elements[$i]." ";
                    }
                    $this->location=trim($this->location);
                    break;
                case (self::TYPE_HELP):
                case (self::TYPE_STATUS):
                    if ($elements[1] && preg_match(self::REGEX_IDENTITY, $elements[1]))
                    { 
                        $this->identifier=$elements[1];
                        $messageStartIndex = 2;
                    }
                    else
                    {
                        $messageStartIndex=1;
                    }
                    for ($i = $messageStartIndex; $i<=count($elements); $i++){
                        $this->message.=$elements[$i]." ";
                    }
                    $this->message=trim($this->message);
                    break;
                case (self::TYPE_ALL_CLEAR):
                    break;
            }
        }

        private function find_message_type($token)
        {
            $token = strtolower($token);
            foreach(explode(",",self::TOKENS_TYPE_LOCATION) as $t)
            {
                if($token==$t) return self::TYPE_LOCATION;
            }

            foreach(explode(",",self::TOKENS_TYPE_CHECKIN) as $t)
            {
                if($token==$t) return self::TYPE_CHECK_IN;
            }

            foreach(explode(",",self::TOKENS_TYPE_CHECKOUT) as $t)
            {
                if($token==$t) return self::TYPE_CHECKOUT;
            }

            foreach(explode(",",self::TOKENS_TYPE_HELP) as $t)
            {
                if($token==$t) return self::TYPE_HELP;
            }

            foreach(explode(",",self::TOKENS_TYPE_ALL_CLEAR) as $t)
            {
                if($token==$t) return self::TYPE_ALL_CLEAR;
            }

            foreach(explode(",",self::TOKENS_TYPE_STATUS) as $t)
            {
                if($token==$t) return self::TYPE_STATUS;
            }
            return null;

        }

    }
?>
