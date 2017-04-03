<?php

namespace Sjoerdmaessen\PDODebug;

class DoctrineQuery
{
    /**
     * Enables the user to see what a parsed prepared statement might look like
     * only for debugging purposes!
     *
     * @param string|\Doctrine\ORM\NativeQuery $query
     * @param array $params
     * @return string
     */
    static public function getQuery($query, $params = array())
    {
        if($query instanceof \Doctrine\ORM\NativeQuery) {
            $params = [];
            /** @var Doctrine\ORM\Query\Parameter; $param */
            foreach($query->getParameters()->getValues() as $param) {
                $params[$param->getName()] = $param->getValue();
            }
            $query = $query->getSQL();
        }

        // Best effort converting parameter values
        foreach ($params as $key => $value) {
            $newKey = ':' . $key;
            unset($params[$key]);
            if (is_string($value)) {
                $value = '"' . $value . '"';
            }
            if (is_bool($value)) {
                $value = (int) $value;
            }
            if (is_null($value)) {
                $value = 'NULL';
            }
            $params[$newKey] = $value;
        }
        uasort(
            $params, function ($first, $second) {
            return strlen($first) - strlen($second);
        }
        );

        // Return the parsed query
        return str_replace(array_keys($params), array_values($params), $query);
    }
}