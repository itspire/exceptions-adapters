<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Adapter\Test\Unit\Webservice;

use Itspire\Exception\Adapter\Webservice\WebserviceExceptionApiAdapter;
use Itspire\Exception\Adapter\Webservice\WebserviceExceptionApiAdapterInterface;
use Itspire\Exception\Http\Definition\HttpExceptionDefinition;
use Itspire\Exception\Http\HttpException;
use Itspire\Exception\Serializer\Model\Api\ApiException;
use Itspire\Exception\Serializer\Model\Api\Webservice\ApiWebserviceException;
use Itspire\Exception\Serializer\Model\Api\Webservice\ApiWebserviceExceptionInterface;
use Itspire\Exception\Webservice\Definition\WebserviceExceptionDefinition;
use Itspire\Exception\Webservice\WebserviceException;
use Itspire\Exception\Webservice\WebserviceExceptionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebserviceExceptionAdapterTest extends TestCase
{
    /** @var MockObject|TranslatorInterface $translatorMock */
    private $translatorMock;
    private ?WebserviceExceptionApiAdapterInterface $webserviceExceptionAdapter = null;
    private ?WebserviceExceptionInterface $businessWebserviceException = null;
    private ?ApiWebserviceExceptionInterface $apiWebserviceException = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->businessWebserviceException = new WebserviceException(
            new WebserviceExceptionDefinition(WebserviceExceptionDefinition::TRANSFORMATION_ERROR),
            []
        );

        $this->apiWebserviceException = new ApiWebserviceException();
        $this->apiWebserviceException
            ->setCode('TRANSFORMATION_ERROR')
            ->setMessage('A transformation exception occurred');

        $this->translatorMock = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        $this->webserviceExceptionAdapter = new WebserviceExceptionApiAdapter($this->translatorMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->webserviceExceptionAdapter,
            $this->translatorMock,
            $this->businessWebserviceException,
            $this->apiWebserviceException
        );

        parent::tearDown();
    }

    /** @test */
    public function adaptBusinessToApiWithUnsupportedClassTest(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Adapter %s does not support %s class', WebserviceExceptionApiAdapter::class, HttpException::class)
        );

        $this->webserviceExceptionAdapter->adaptBusinessToApi(
            new HttpException(new HttpExceptionDefinition(HttpExceptionDefinition::HTTP_LOCKED))
        );
    }

    /** @test */
    public function adaptBusinessToApiTest(): void
    {
        $this->translatorMock
            ->expects(static::once())
            ->method('trans')
            ->with($this->businessWebserviceException->getExceptionDefinition()->getDescription(), [], 'exceptions')
            ->willReturn($this->apiWebserviceException->getMessage());

        static::assertEquals(
            $this->apiWebserviceException,
            $this->webserviceExceptionAdapter->adaptBusinessToApi($this->businessWebserviceException)
        );
    }

    /** @test */
    public function adaptBusinessToApiWithDetailsTest(): void
    {
        $this->businessWebserviceException = new WebserviceException(
            new WebserviceExceptionDefinition(WebserviceExceptionDefinition::TRANSFORMATION_ERROR),
            ['testDetails1', 'testDetails2']
        );

        $this->apiWebserviceException->addDetail('testDetails1')->addDetail('testDetails2');

        $this->translatorMock
            ->expects(static::at(0))
            ->method('trans')
            ->with($this->businessWebserviceException->getExceptionDefinition()->getDescription(), [], 'exceptions')
            ->willReturn($this->apiWebserviceException->getMessage());

        $this->translatorMock
            ->expects(static::at(1))
            ->method('trans')
            ->with('testDetails1', [], 'exceptions')
            ->willReturn('testDetails1');

        $this->translatorMock
            ->expects(static::at(2))
            ->method('trans')
            ->with('testDetails2', [], 'exceptions')
            ->willReturn('testDetails2');

        static::assertEquals(
            $this->apiWebserviceException,
            $this->webserviceExceptionAdapter->adaptBusinessToApi($this->businessWebserviceException)
        );
    }

    /** @test */
    public function adaptApiToBusinessWithUnsupportedClassTest(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Adapter %s does not support %s class',
                WebserviceExceptionApiAdapter::class,
                ApiException::class
            )
        );

        $this->webserviceExceptionAdapter->adaptApiToBusiness(new ApiException());
    }

    /** @test */
    public function adaptApiToBusinessTest(): void
    {
        $businessWebserviceException = $this->webserviceExceptionAdapter->adaptApiToBusiness(
            $this->apiWebserviceException
        );

        static::assertEquals(
            $this->businessWebserviceException->getExceptionDefinition()->getCode(),
            $businessWebserviceException->getExceptionDefinition()->getCode()
        );

        static::assertEquals(
            $this->apiWebserviceException->getDetails(),
            $businessWebserviceException->getDetails()
        );
    }

    /** @test */
    public function adaptApiToBusinessWithDetailsTest(): void
    {
        $this->apiWebserviceException->addDetail('detail1')->addDetail('detail2');

        $businessWebserviceException = $this->webserviceExceptionAdapter->adaptApiToBusiness(
            $this->apiWebserviceException
        );

        static::assertEquals($this->apiWebserviceException->getDetails(), $businessWebserviceException->getDetails());
    }
}
