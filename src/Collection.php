<?php

namespace Fureev\Trees;

use Illuminate\Database\Eloquent\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Model;

class Collection extends BaseCollection
{
    /**
     * Build a tree from a list of nodes. Each item will have set children relation.
     *
     * If `$fromNode` is provided, the tree will contain only descendants of that node.
     *
     * @param Model|string|int|null $fromNode
     *
     * @return $this
     */
    public function toTree($fromNode = null): self
    {
        if ($this->isEmpty()) {
            return new static();
        }

        $this->linkNodes(false);
        $items = [];

        if ($fromNode) {
            if ($fromNode instanceof Model) {
                $fromNode = $fromNode->getKey();
            }
        }

        /** @var Model|NestedSetTrait $node */
        foreach ($this->items as $node) {
            if ($node->parentValue() === $fromNode) {
                $items[] = $node;
            }
        }

        return new static($items);
    }

    /**
     * Fill `parent` and `children` relationships for every node in the collection.
     *
     * This will overwrite any previously set relations.
     *
     * Для того, что бы не делать лишние запросы в бд по этим релейшенам
     *
     * @param bool $setParentRelations
     *
     * @return $this
     */
    public function linkNodes($setParentRelations = true): self
    {
        if ($this->isEmpty()) {
            return $this;
        }

        $groupedNodes = $this->groupBy($this->first()->parentAttribute()->name());

        /** @var NestedSetTrait|Model $node */
        foreach ($this->items as $node) {
            if (!$node->parentValue()) {
                $node->setRelation('parent', null);
            }

            $children = $groupedNodes->get($node->getKey(), []);
            if ($setParentRelations) {
                /** @var Model|NestedSetTrait $child */
                foreach ($children as $child) {
                    $child->setRelation('parent', $node);
                }
            }

            $node->setRelation('children', static::make($children));
        }

        return $this;
    }

    /**
     * Returns all root-nodes
     *
     * @return $this
     */
    public function getRoots(): self
    {
        return $this->filter(
            static function ($item) {
                return $item->parentValue() === null;
            }
        );
    }
}
