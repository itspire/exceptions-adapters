<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Adapter\Webservice;

use Itspire\Exception\Adapter\AbstractExceptionApiAdapter;
use Itspire\Exception\ExceptionInterface;
use Itspire\Exception\Serializer\Model\Api\ApiExceptionInterface;
use Itspire\Exception\Serializer\Model\Api\Webservice\ApiWebserviceExceptionInterface;
use Itspire\Exception\Webservice\WebserviceExceptionInterface;

/**
 * @method WebserviceExceptionInterface adaptApiToBusiness(ApiWebserviceExceptionInterface $apiObject)
 * @method ApiWebserviceExceptionInterface adaptBusinessToApi(WebserviceExceptionInterface $businessObject)
 */
abstract class AbstractWebserviceExceptionApiAdapter extends AbstractExceptionApiAdapter implements
    WebserviceExceptionApiAdapterInterface
{
    /**
     * @param ApiWebserviceExceptionInterface $apiException
     * @param WebserviceExceptionInterface $businessException
     */
    final protected function adaptApiObject(
        ApiExceptionInterface $apiException,
        ExceptionInterface $businessException
    ): void {
        foreach ($apiException->getDetails() as $detail) {
            $businessException->addDetail($detail);
        }
    }

    /**
     * @param WebserviceExceptionInterface $businessException
     * @param ApiWebserviceExceptionInterface $apiException
     */
    final protected function adaptBusinessObject(
        ExceptionInterface $businessException,
        ApiExceptionInterface $apiException
    ): void {
        foreach ($businessException->getDetails() as $detail) {
            $apiException->addDetail(
                null !== $this->getTranslationDomain()
                    ? $this->translator->trans($detail, [], $this->getTranslationDomain())
                    : $detail
            );
        }
    }
}
