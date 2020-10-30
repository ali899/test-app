<?php



namespace App\Helpers;

class ResponseStatus
{
    const NOT_FOUND = 404;
    const NOT_ALLOWED= 405 ;
    const NOT_AUTHORIZED = 401;
    const ACCESS_FORBIDDEN = 403;
    const BAD_REQUEST = 400;
    const VALIDATION_ERROR = 422;
    const GENERAL_ERROR = 500;
    const CREATED = 201;
    const SUCCESS = 200;
}
