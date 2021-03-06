<?php

namespace Mocka;

class FunctionMock {

    /** @var \Closure */
    private $_defaultClosure;

    /** @var \Closure[] */
    private $_orderedClosures;

    /** @var int */
    private $_counter;

    public function __construct() {
        $this->_orderedClosures = array();
        $this->_defaultClosure = function () {
        };
        $this->_counter = 0;
    }

    /**
     * @param mixed|\Closure $body
     * @return static
     */
    public function set($body) {
        $this->_defaultClosure = $this->_normalizeBody($body);
        return $this;
    }

    /**
     * @param int|int[] $at
     * @param string|\Closure $body
     * @return static
     */
    public function at($at, $body) {
        $ats = (array) $at;
        foreach ($ats as $at) {
            $at = (int) $at;
            $closure = $this->_normalizeBody($body);
            $this->_orderedClosures[$at] = $closure;
        }
        return $this;
    }

    /**
     * @param array|null $arguments
     * @return mixed
     */
    public function invoke(array $arguments = null) {
        $arguments = (array) $arguments;
        $closure = $this->_getClosure($this->_counter);
        $this->_counter++;
        return call_user_func_array($closure, $arguments);
    }

    /**
     * @return int
     */
    public function getCallCount() {
        return $this->_counter;
    }

    public function __invoke() {
        return $this->invoke(func_get_args());
    }

    /**
     * @param int $at
     * @return \Closure
     */
    private function _getClosure($at) {
        $at = (int) $at;
        if (array_key_exists($this->_counter, $this->_orderedClosures)) {
            return $this->_orderedClosures[$at];
        }
        return $this->_defaultClosure;
    }

    /**
     * @param mixed|\Closure $body
     * @return \Closure
     */
    private function _normalizeBody($body) {
        $closure = $body;
        if (!$closure instanceof \Closure) {
            $closure = function () use ($body) {
                return $body;
            };
        }
        return $closure;
    }
}
