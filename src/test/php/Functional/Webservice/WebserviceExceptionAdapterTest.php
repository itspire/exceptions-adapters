<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Adapter\Test\Functional\Webservice;

use Itspire\Exception\Adapter\Webservice\WebserviceExceptionApiAdapter;
use Itspire\Exception\Adapter\Webservice\WebserviceExceptionApiAdapterInterface;
use Itspire\Exception\Serializer\Model\Api\Webservice\ApiWebserviceException;
use Itspire\Exception\Serializer\Model\Api\Webservice\ApiWebserviceExceptionInterface;
use Itspire\Exception\Webservice\Definition\WebserviceExceptionDefinition;
use Itspire\Exception\Webservice\WebserviceException;
use Itspire\Exception\Webservice\WebserviceExceptionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebserviceExceptionAdapterTest extends TestCase
{
    private static ?TranslatorInterface $translator = null;
    private ?WebserviceExceptionApiAdapterInterface $webserviceExceptionAdapter = null;
    private ?WebserviceExceptionInterface $businessWebserviceException = null;
    private ?ApiWebserviceExceptionInterface $apiWebserviceException = null;

    public static function setUpBeforeClass(): void
    {
        if (null === self::$translator) {
            self::$translator = new Translator('en');
            self::$translator->addLoader('yml', new YamlFileLoader());

            $finder = new Finder();
            $finder->files()->in(realpath('src/test/resources/translations'));

            foreach ($finder as $file) {
                $fileNameParts = explode('.', $file->getFilename());
                self::$translator->addResource('yml', $file->getRealPath(), $fileNameParts[1], $fileNameParts[0]);
            }
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::$translator = null;
    }

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

        $this->webserviceExceptionAdapter = new WebserviceExceptionApiAdapter(self::$translator);
    }

    protected function tearDown(): void
    {
        unset($this->webserviceExceptionAdapter, $this->businessWebserviceException, $this->apiWebserviceException);

        parent::tearDown();
    }

    /** @test */
    public function adaptBusinessToApiTest(): void
    {
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

        $this->apiWebserviceException
            ->addDetail('My first detailed information')
            ->addDetail('My second detailed information');

        static::assertEquals(
            $this->apiWebserviceException,
            $this->webserviceExceptionAdapter->adaptBusinessToApi($this->businessWebserviceException)
        );
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
        $this->apiWebserviceException->addDetail('testDetails1')->addDetail('testDetails2');

        $businessWebserviceException = $this->webserviceExceptionAdapter->adaptApiToBusiness(
            $this->apiWebserviceException
        );

        static::assertEquals($this->apiWebserviceException->getDetails(), $businessWebserviceException->getDetails());
    }
}
