<?php

declare(strict_types=1);

namespace Zlikavac32\DoctrineEnum\Tests\Integration\DBAL\Types;

use Closure;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\AnswerEntity;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\YesNoEnum;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\YesNoEnumType;

class EnumTypeORMIntegrationTest extends TestCase
{
    /**
     * @var EntityManager
     */
    private static $entityManager;

    public function setUp(): void
    {
        if (
            false === extension_loaded('sqlite3')
            ||
            false === extension_loaded('pdo_sqlite')
        ) {
            $this->markTestSkipped('sqlite3/pdo_sqlite extensions are required for this test to run');
        }
    }

    public static function setUpBeforeClass(): void
    {
        //This will remain in the memory until the script ends
        Type::addType('enum_yes_no', YesNoEnumType::class);
    }

    public static function tearDownAfterClass(): void
    {
        if (null === self::$entityManager) {
            return;
        }

        self::$entityManager = null;
    }

    public function testThatCreateTableCanBeGenerated(): void
    {
        $entityManager = self::entityManager();

        $schemaTool = new SchemaTool($entityManager);

        $createSchemaSqls = $schemaTool->getCreateSchemaSql(
            [
                $entityManager->getClassMetadata(AnswerEntity::class),
            ]
        );

        $this->assertArrayHasKey(0, $createSchemaSqls);

        $this->assertContains('answer VARCHAR(32) DEFAULT NULL --(DC2Type:enum_yes_no)', $createSchemaSqls[0]);
    }

    public function testThatEntityWithNonNullEnumCanBeCreated(): void
    {
        $this->runWithinAnswerContent(
            function (EntityManager $entityManager): void {
                $entity = $this->persistAndReturnEntity($entityManager, YesNoEnum::YES());

                $this->assertGreaterThan(0, $entity->getId());
            }
        );
    }

    public function testThatEntityWithNullEnumCanBeCreated(): void
    {
        $this->runWithinAnswerContent(
            function (EntityManager $entityManager): void {
                $entity = $this->persistAndReturnEntity($entityManager, null);

                $this->assertGreaterThan(0, $entity->getId());
            }
        );
    }

    public function testThatEntityWithNonNullEnumCanBeLoaded(): void
    {
        $this->runWithinAnswerContent(
            function (EntityManager $entityManager): void {
                $entity = $this->persistAndReturnEntity($entityManager, YesNoEnum::NO());

                $entityManager->detach($entity);

                /* @var AnswerEntity $loadedEntity */
                $loadedEntity = $entityManager->getRepository(AnswerEntity::class)
                    ->find($entity->getId());

                $this->assertSame($entity->getId(), $loadedEntity->getId());
                $this->assertSame($entity->getAnswer(), $loadedEntity->getAnswer());
            }
        );
    }

    public function testThatEntityWithNullEnumCanBeLoaded(): void
    {
        $this->runWithinAnswerContent(
            function (EntityManager $entityManager): void {
                $entity = $this->persistAndReturnEntity($entityManager, null);

                $entityManager->detach($entity);

                /* @var AnswerEntity $loadedEntity */
                $loadedEntity = $entityManager->getRepository(AnswerEntity::class)
                    ->find($entity->getId());

                $this->assertSame($entity->getId(), $loadedEntity->getId());
                $this->assertSame($entity->getAnswer(), $loadedEntity->getAnswer());
            }
        );
    }

    private function runWithinAnswerContent(Closure $testClosure): void
    {
        $entityManager = self::entityManager();

        $schemaTool = new SchemaTool($entityManager);

        $schemaTool->createSchema(
            [
                $entityManager->getClassMetadata(AnswerEntity::class),
            ]
        );

        try {
            $testClosure($entityManager);
        } finally {
            $schemaTool->dropSchema(
                [
                    $entityManager->getClassMetadata(AnswerEntity::class),
                ]
            );
        }
    }

    private function persistAndReturnEntity(EntityManager $entityManager, ?YesNoEnum $answer): AnswerEntity
    {
        $entity = (new AnswerEntity())
            ->setAnswer(YesNoEnum::YES());

        $entityManager->persist($entity);
        $entityManager->flush();

        return $entity;
    }

    private function entityManager(): EntityManager
    {
        if (null === self::$entityManager) {
            $paths = [__DIR__ . '/../../../Fixtures'];
            $isDevMode = true;

            $dbParams = [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ];

            $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
            self::$entityManager = EntityManager::create($dbParams, $config);
        }

        return self::$entityManager;
    }
}
