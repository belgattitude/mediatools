<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Video\Exception;

use Soluble\MediaTools\Common\Exception\RuntimeException;

class NoOutputGeneratedException extends RuntimeException implements ConverterExceptionInterface
{
}
