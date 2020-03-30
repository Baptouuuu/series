<?php
declare(strict_types = 1);

namespace Tests\Series\Storage;

use Series\{
    Storage\Filesystem,
    Storage,
};
use Innmind\Filesystem\Adapter\MemoryAdapter;
use Innmind\Immutable\SetInterface;
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    private $storage;
    private $adapter;

    public function setUp(): void
    {
        $this->storage = new Filesystem(
            $this->adapter = new MemoryAdapter,
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
        $this->assertFalse($this->adapter->has('foo.txt'));
        $this->assertSame($this->storage, $this->storage->add('watev'));
        $this->assertTrue($this->adapter->has('foo.txt'));
        $this->storage->add('another');
        $this->assertSame(
            "watev\nanother",
            (string) $this->adapter->get('foo.txt')->content()
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
            (string) $this->adapter->get('foo.txt')->content()
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

        $this->assertInstanceOf(SetInterface::class, $all);
        $this->assertSame('string', (string) $all->type());
        $this->assertSame(['foo', 'bar', 'baz'], $all->toPrimitive());
    }
}
