<?php
declare(strict_types = 1);

namespace Series\Visitor;

use Innmind\Xml\{
    Element,
    Node,
    Attribute
};
use Innmind\Immutable\{
    Set,
    Str,
};

/**
 * Recursively walk through a dom to find nodes containing episodes information
 *
 * Only work for a dom provided by pogdesign.co.uk/cat
 */
final class Episodes
{
    private ?Attribute $currentDay = null;

    /**
     * @return Set<Element>
     */
    public function __invoke(Node $element): Set
    {
        $elements = Set::of(Element::class);

        if (
            $element instanceof Element &&
            $element->attributes()->contains('class')
        ) {
            $class = Str::of($element->attributes()->get('class')->value());

            if ($class->matches('/^ep( t[12])? info/')) {
                return $elements->add(
                    $element->addAttribute($this->currentDay)
                );
            } else if ($class->matches('/^(to)?day$/')) {
                $this->currentDay = $element->attributes()->get('id');
            }
        }

        return $element
            ->children()
            ->filter(static function(Node $node): bool {
                return $node instanceof Element;
            })
            ->reduce(
                $elements,
                function(Set $elements, Element $element): Set {
                    return $elements->merge($this($element));
                }
            );
    }
}
