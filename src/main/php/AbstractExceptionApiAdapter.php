<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Adapter;

use Itspire\Exception\Definition\ExceptionDefinitionInterface;
use Itspire\Exception\ExceptionInterface;
use Itspire\Exception\Serializer\Model\Api\ApiExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractExceptionApiAdapter implements ExceptionApiAdapterInterface
{
    protected ?TranslatorInterface $translator = null;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    final public function adaptApiToBusiness(ApiExceptionInterface $apiException): ExceptionInterface
    {
        $this->supports(get_class($apiException));

        $businessExceptionClass = $this->getBusinessClass();

        /** @var ExceptionDefinitionInterface $exceptionDefinitionClass */
        $exceptionDefinitionClass = $this->getExceptionDefinitionClass();

        /** @var ExceptionInterface $businessException */
        $businessException = new $businessExceptionClass(
            $exceptionDefinitionClass::resolveCode($apiException->getCode())
        );

        $this->adaptApiObject($apiException, $businessException);

        return $businessException;
    }

    final public function adaptBusinessToApi(ExceptionInterface $businessException): ApiExceptionInterface
    {
        $this->supports(get_class($businessException));

        $apiExceptionClass = $this->getApiClass();

        /** @var ApiExceptionInterface $apiException */
        $apiException = (new $apiExceptionClass())
            ->setCode($businessException->getExceptionDefinition()->getCode())
            ->setMessage(
                null !== $this->getTranslationDomain()
                    ? $this->translator->trans(
                        $businessException->getExceptionDefinition()->getDescription(),
                        [],
                        $this->getTranslationDomain()
                    )
                    : $businessException->getExceptionDefinition()->getDescription()
            );

        $this->adaptBusinessObject($businessException, $apiException);

        return $apiException;
    }

    final public function supports(string $class): void
    {
        if (false === in_array($class, [$this->getApiClass(), $this->getBusinessClass()], true)) {
            throw new \InvalidArgumentException(sprintf('Adapter %s does not support %s class', static::class, $class));
        }
    }

    /** @return string Fully qualified Exception Definition class name */
    abstract protected function getExceptionDefinitionClass(): string;

    /** @codeCoverageIgnore */
    protected function adaptApiObject(ApiExceptionInterface $apiException, ExceptionInterface $businessException): void
    {
    }

    /** @codeCoverageIgnore */
    protected function adaptBusinessObject(
        ExceptionInterface $businessException,
        ApiExceptionInterface $apiException
    ): void {
    }

    protected function getTranslationDomain(): ?string
    {
        return 'exceptions';
    }
}
