<?php

declare(strict_types=1);

/**
 * @see       https://github.com/soluble-io/soluble-mediatools for the canonical repository
 *
 * @copyright Copyright (c) 2018-2020 Sébastien Vanvelthem. (https://github.com/belgattitude)
 * @license   https://github.com/soluble-io/soluble-mediatools/blob/master/LICENSE.md MIT
 */

namespace Soluble\MediaTools\Common\Service;

use Soluble\MediaTools\Video\Exception\UnsetParamException;

interface ActionParamInterface
{
    /**
     * Return the internal array holding params.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Test whether a param is built-in or valid.
     */
    public function isParamValid(string $paramName): bool;

    /**
     * Return a param, throw an exception if the param has not been defined yet or
     * use $default if it was set.
     *
     * @param mixed $default Will return default value instead of throwing exception
     *
     * @return mixed
     *
     * @throws UnsetParamException
     */
    public function getParam(string $paramName, $default = null);

    public function hasParam(string $paramName): bool;
}
