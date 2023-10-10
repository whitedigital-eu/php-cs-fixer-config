<?php declare(strict_types = 1);

namespace WhiteDigital\Config\DataFixture;

use BackedEnum;
use Composer\InstalledVersions;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use LogicException;
use WhiteDigital\Config\Faker;
use WhiteDigital\Config\Traits;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;

abstract class AbstractFixture extends Fixture implements DependentFixtureInterface
{
    use Traits\Common;
    use Traits\FakerTrait;

    public static array $references;

    public function __construct(Faker $faker)
    {
        self::setFaker($faker);
    }

    public function getDependencies(): array
    {
        return [
            BaseClassifierFixture::class,
        ];
    }

    public function reference(BaseEntity $fixture, ?int $key = null): void
    {
        $name = $fixture::class;
        if (null !== $key) {
            $name .= $key;
        }

        $this->addReference($name, $fixture);
        self::$references[$fixture::class][] = $name;
    }

    /**
     * @return BaseEntity|null
     */
    protected function getEntity(string $fixture, ?int $i = null): ?object
    {
        $key = (static::$references[$fixture][$i ?? self::randomArrayKey(static::$references[$fixture])] ?? null) ?? null;

        return match ($key) {
            null => null,
            default => $this->getReference($key),
        };
    }

    /**
     * @return BaseEntity[]
     */
    protected function getEntityReferences(string $fixture): array
    {
        return static::$references[$fixture] ?? [];
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    protected function getNode(string $type): object
    {
        if (!InstalledVersions::isInstalled('whitedigital-eu/site-tree')) {
            throw new LogicException('SiteTree is missing. Try running "composer require whitedigital-eu/site-tree".');
        }

        return $this->getReference('node' . $type . self::randomArrayKey(\WhiteDigital\SiteTree\DataFixture\SiteTreeFixture::$references[$type]));
    }

    protected function getImage(): object
    {
        return $this->getReference('wdFile_image');
    }

    protected function getFile(): object
    {
        return $this->getReference('wdFile_text');
    }

    /**
     * @return BackedEnum
     */
    protected function getClassifier(BackedEnum $type): object
    {
        return $this->getReference(($values = BaseClassifierFixture::$references[BaseClassifierFixture::class][$type->name])[self::randomArrayKey($values)]);
    }

    protected function getClassifierReferences(BackedEnum $type): array
    {
        return BaseClassifierFixture::$references[BaseClassifierFixture::class][$type->name] ?? [];
    }
}
