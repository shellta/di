<?php
declare(strict_types=1);


namespace Mo\Container\Exception;


use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{

}
