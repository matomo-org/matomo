<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * WeakMap polyfill for PHP < 8.0, required by composer dependencies that are
 * downgraded to PHP 7.2 (see .github/scripts/vendor-downgrade).
 *
 * Unlike the native WeakMap, this implementation holds strong references to its
 * keys, so entries are only released when explicitly unset or when the map itself
 * is garbage collected. That is acceptable for the short-lived maps used by our
 * dependencies, but means this polyfill must not be relied on for weak reference
 * semantics.
 */

if (\PHP_VERSION_ID < 80000 && !class_exists('WeakMap', false)) {
    // phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace -- polyfills the global WeakMap class
    final class WeakMap implements ArrayAccess, Countable, IteratorAggregate
    {
        /**
         * @var array<int, object> keeps the keys alive, indexed by spl_object_id
         */
        private $objects = [];

        /**
         * @var array<int, mixed> values indexed by spl_object_id of their key
         */
        private $values = [];

        public function offsetExists($object): bool
        {
            $this->assertValidKey($object);

            return array_key_exists(spl_object_id($object), $this->values);
        }

        /**
         * Returns by reference, so that e.g. `$map[$object][] = $item;` works like
         * it does with the native WeakMap.
         *
         * @return mixed
         */
        public function &offsetGet($object)
        {
            $this->assertValidKey($object);

            $objectId = spl_object_id($object);

            if (!array_key_exists($objectId, $this->values)) {
                throw new Error('Object not found');
            }

            return $this->values[$objectId];
        }

        public function offsetSet($object, $value): void
        {
            $this->assertValidKey($object);

            $objectId = spl_object_id($object);

            $this->objects[$objectId] = $object;
            $this->values[$objectId] = $value;
        }

        public function offsetUnset($object): void
        {
            $this->assertValidKey($object);

            $objectId = spl_object_id($object);

            unset($this->objects[$objectId], $this->values[$objectId]);
        }

        public function count(): int
        {
            return count($this->values);
        }

        public function getIterator(): Iterator
        {
            foreach ($this->objects as $objectId => $object) {
                yield $object => $this->values[$objectId];
            }
        }

        /**
         * @param mixed $object
         */
        private function assertValidKey($object): void
        {
            if (!is_object($object)) {
                throw new TypeError('WeakMap key must be an object');
            }
        }
    }
}
