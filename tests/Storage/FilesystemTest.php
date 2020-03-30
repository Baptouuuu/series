<?php
declare(strict_types = 1);

namespace Tests\Series\Storage;

use Series\{
    Storage\Filesystem,
    Storage,
};
use Innmind\Filesystem\{
    Adapter\InMemory,
    Name,
};
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    private $storage;
    private $adapter;

    public function setUp(): void
    {
        $this->storage = new Filesystem(
            $this->adapter = new InMemory,
            'foo.txt'
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Storage::class,
            $this->storage
        );
    }

    public function testAdd()
    {
        $this->assertFalse($this->adapter->contains(new Name('foo.txt')));
        $this->assertSame($this->storage, $this->storage->add('watev'));
        $this->assertTrue($this->adapter->contains(new Name('foo.txt')));
        $this->storage->add('another');
        $this->assertSame(
            "watev\nanother",
            $this->adapter->get(new Name('foo.txt'))->content()->toString()
        );
    }

    public function testRemove()
    {
        $this
            ->storage
            ->add('foo')
            ->add('bar')
            ->add('baz');

        $this->assertSame($this->storage, $this->storage->remove('bar'));
        $this->assertSame(
            "foo\nbaz",
            $this->adapter->get(new Name('foo.txt'))->content()->toString()
        );
    }

    public function testAll()
    {
        $this
            ->storage
            ->add('foo')
            ->add('bar')
            ->add('baz');

        $all = $this->storage->all();

        $this->assertInstanceOf(Set::class, $all);
        $this->assertSame('string', (string) $all->type());
        $this->assertSame(['foo', 'bar', 'baz'], unwrap($all));
    }
}
