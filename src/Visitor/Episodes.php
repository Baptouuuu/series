<?php
declare(strict_types = 1);

namespace Series\Visitor;

use Innmind\Xml\{
    ElementInterface,
    NodeInterface
};
use Innmind\Immutable\{
    SetInterface,
    Set,
    Str
};

/**
 * Recursively walk through a dom to find nodes containing episodes information
 *
 * Only work for a dom provided by pogdesign.co.uk/cat
 */
final class Episodes
{
    /**
     * @return SetInterface<ElementInterface>
     */
    public function __invoke(NodeInterface $element): SetInterface
    {
        $elements = new Set(ElementInterface::class);

        if (
            $element instanceof ElementInterface &&
            $element->attributes()->contains('class')
        ) {
            $class = new Str($element->attributes()->get('class')->value());

            if ($class->matches('/^ep( t[12])? info/')) {
                return $elements->add($element);
            }
        }

        return $element
            ->children()
            ->filter(static function(int $position, NodeInterface $node): bool {
                return $node instanceof ElementInterface;
            })
            ->reduce(
                $elements,
                function(Set $elements, int $position, ElementInterface $element): Set {
                    return $elements->merge($this($element));
                }
            );
    }
}
