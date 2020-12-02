<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Adapter;

use Itspire\Exception\ExceptionInterface;
use Itspire\Exception\Serializer\Model\Api\ApiExceptionInterface;

interface ExceptionApiAdapterInterface
{
    public function adaptApiToBusiness(ApiExceptionInterface $apiException): ExceptionInterface;

    public function adaptBusinessToApi(ExceptionInterface $businessException): ApiExceptionInterface;

    /** @return string Fully qualified Api class name */
    public function getApiClass(): string;
}
