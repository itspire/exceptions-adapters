<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Adapter\Test\Fixtures\Webservice;

use Itspire\Exception\Adapter\Test\Fixtures\Model\Api\Webservice\ApiWebserviceException;
use Itspire\Exception\Adapter\Test\Fixtures\Model\Business\Model\ExtendedWebserviceException;
use Itspire\Exception\Adapter\Webservice\AbstractWebserviceExceptionApiAdapter;
use Itspire\Exception\Webservice\Definition\WebserviceExceptionDefinition;

class WebserviceExceptionApiAdapter extends AbstractWebserviceExceptionApiAdapter
{
    public function getApiClass(): string
    {
        return ApiWebserviceException::class;
    }

    public function getBusinessClass(): string
    {
        return ExtendedWebserviceException::class;
    }

    /** @return string Fully qualified Exception Definition class name */
    protected function getExceptionDefinitionClass(): string
    {
        return WebserviceExceptionDefinition::class;
    }
}
