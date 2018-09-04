<?php
declare(strict_types=1);

namespace ArrayIterator\Coinvestasi\Core;

/**
 * Class Hook
 * @package ArrayIterator\Coinvestasi\Core
 */
class Hook
{
    /**
     * @var int
     */
    const DEFAULT_PRIORITY = 10;

    /**
     * @var array[]|callable[][][]
     */
    protected $collector = [];

    /**
     * @var int[]
     */
    protected $called = [];

    /**
     * @var int[]
     */
    protected $applied = [];

    /**
     * @var string[]
     */
    protected $current = [];


    /**
     * Hook constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $hookName
     * @param callable $callable
     * @param int $priority
     */
    public function add(
        string $hookName,
        callable $callable,
        int $priority = self::DEFAULT_PRIORITY
    ) {
        if (!($hookName = $this->sanitizeHookName($hookName))) {
            throw new \InvalidArgumentException(
                'Invalid hook name given'
            );
        }

        $id = $this->getUniqueId($callable);
        $this->collector[$hookName][$priority][$id] = $callable;
    }

    /**
     * @param string $hookName
     * @param $value
     * @param mixed ...$params
     * @return mixed
     */
    public function apply(string $hookName, $value, ...$params)
    {
        $hookName = $this->sanitizeHookName($hookName);
        if (!$hookName || !isset($this->collector[$hookName])) {
            return $value;
        }

        if (!isset($this->applied[$hookName])) {
            $this->applied[$hookName] = 1;
        } else {
            $this->applied[$hookName]++;
        }

        $this->current[] = $hookName;
        reset($this->collector);
        do {
            foreach (current($this->collector[$hookName]) as $callable) {
                $callable($value, ...$params);
            }
        } while (next($this->collector[$hookName]) !== false);
        array_pop($this->current);

        return $value;
    }

    /**
     * @param string $hookName
     * @param mixed ...$params
     * @return bool
     */
    public function call(string $hookName, ...$params)
    {
        $hookName = $this->sanitizeHookName($hookName);
        if (!$hookName || !isset($this->collector[$hookName])) {
            return false;
        }

        if (!isset($this->called[$hookName])) {
            $this->called[$hookName] = 1;
        } else {
            $this->called[$hookName]++;
        }

        $this->current[] = $hookName;
        reset($this->collector[$hookName]);
        do {
            foreach (current($this->collector[$hookName]) as $callable) {
                $callable(...$params);
            }
        } while (next($this->collector[$hookName]) !== false);
        array_pop($this->current);

        return true;
    }

    /**
     * @param string $hookName
     * @param callable|null $callable
     * @param int|null $priority
     * @return bool
     */
    public function remove(
        string $hookName,
        callable $callable = null,
        int $priority = null
    ) {
        $hookName = $this->sanitizeHookName($hookName);
        if (!$hookName || !isset($this->collector[$hookName])) {
            return false;
        }

        $callable = $callable ?: $this->getUniqueId($callable);
        if (!$callable && $priority === null) {
            return false;
        }

        if ($priority !== null) {
            if (!isset($this->collector[$hookName][$priority])
                || !$callable
                || !isset($this->collector[$hookName][$priority][$callable])
            ) {
                return false;
            }
            if (!$callable) {
                unset($this->collector[$hookName][$priority]);
                return true;
            }
            unset($this->collector[$hookName][$priority][$callable]);
            return true;
        }

        $ret = false;
        foreach ($this->collector[$hookName] as $priority => $v) {
            if (isset($v[$callable])) {
                $ret = true;
                unset($this->collector[$hookName][$priority][$callable]);
            }
        }

        return $ret;
    }

    /**
     * @param string $hookName
     * @param callable|null $callable
     * @return bool
     */
    public function exist(
        string $hookName,
        callable $callable = null
    ) : bool {
        if (!($hookName = $this->sanitizeHookName($hookName))
            || !isset($this->collector[$hookName])
        ) {
            return false;
        }

        if ($callable === null) {
            return true;
        }
        if (!($id = $this->getUniqueId($callable))) {
            return false;
        }

        foreach ($this->collector[$hookName] as $v) {
            if (isset($v[$id])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string|false
     */
    public function current()
    {
        return end($this->current);
    }

    /**
     * @param string $hookName
     * @return int
     */
    public function count(string $hookName) : int
    {
        $hookName = $this->sanitizeHookName($hookName);
        if (!$hookName || !isset($this->collector[$hookName])) {
            return 0;
        }

        return count($this->collector[$hookName]);
    }


    /**
     * @param string $hookName
     * @param int|null $priority
     * @return callable[]|\callable[][]|null
     */
    public function find(
        string $hookName,
        int $priority = null
    ) {
        $hookName = $this->sanitizeHookName($hookName);
        if (!$hookName || !isset($this->collector[$hookName])) {
            return null;
        }

        if ($priority === null) {
            return $this->collector[$hookName];
        }

        return $this->collector[$hookName][$priority];
    }

    /**
     * @param string $key
     * @return bool
     */
    public function sanitizeHookName(string $key)
    {
        return trim($key) ?: false;
    }

    /**
     * @param mixed|callable $callback
     * @return null|string
     */
    private function getUniqueId($callback)
    {
        switch (gettype($callback)) {
            case 'string':
                return $callback;
            case 'object':
                return spl_object_hash($callback);
            case 'array':
                if (count($callback) > 0) {
                    return $callback[0] . '::' . $callback[1];
                }
        }

        return null;
    }

    /**
     * Clear Source hook
     */
    public function clear()
    {
        $this->collector = [];
        $this->current = [];
        $this->applied = [];
        $this->called = [];
    }
}
