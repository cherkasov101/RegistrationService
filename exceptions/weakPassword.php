<?php

class WeakPasswordException extends Exception {
    public function __construct($message = "Weak password", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}