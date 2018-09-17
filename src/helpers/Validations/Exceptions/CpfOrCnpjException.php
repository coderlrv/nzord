<?php
namespace NZord\Helpers\Validations\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class CpfOrCnpjException extends ValidationException
{
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Campo <b>{{name}}</b> deve ser um número válido.',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Campo <b>{{name}}</b> não deve ser um número válido.',
        ],
    ];
}