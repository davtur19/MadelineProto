<?php

namespace danog\MadelineProto\Db;

use danog\MadelineProto\MTProto;

/**
 * Include this trait and call DbPropertiesTrait::initDb to use MadelineProto's database backend for properties.
 *
 * You will have to define a `$dbProperties` static array property, with a list of properties you want to store to a database.
 *
 * @see DbPropertiesFactory For a list of allowed property types
 *
 * @property array<string, DbPropertiesFactory::TYPE_*> $dbProperties
 */
trait DbPropertiesTrait
{
    /**
     * Initialize database instance.
     *
     * @internal
     *
     * @param MTProto $MadelineProto
     * @param boolean $reset
     * @return \Generator
     */
    public function initDb(MTProto $MadelineProto, bool $reset = false): \Generator
    {
        if (empty(static::$dbProperties)) {
            throw new \LogicException(static::class.' must have $dbProperties');
        }
        $dbSettings = $MadelineProto->settings->getDb();
        $prefix = static::getSessionId($MadelineProto);

        foreach (static::$dbProperties as $property => $type) {
            if ($reset) {
                unset($this->{$property});
            } else {
                $table = "{$prefix}_{$property}";
                $this->{$property} = yield DbPropertiesFactory::get($dbSettings, $table, $type, $this->{$property});
            }
        }
    }

    private static function getSessionId(MTProto $madelineProto): string
    {
        $result = $madelineProto->getSelf()['id'] ?? null;
        if (!$result) {
            $result = 'tmp_';
            $result .= \str_replace('0', '', \spl_object_hash($madelineProto));
        }

        $className = \explode('\\', static::class);
        $result .= '_'.\end($className);
        return $result;
    }
}
