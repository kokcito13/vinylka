<?php
class Application_Model_Kernel_Breadcrumbs implements Iterator,Countable {

	protected $breadcrumbs = array();

	public function add($name, $url = '') {
		array_push($this->breadcrumbs, array(
			'name' => $name,
			'url' => $url
		));
	}
	
	public function getBack() {
		return $this->breadcrumbs[sizeof($this->breadcrumbs)-2]['url'];
	}
	
	public function clear() {
		$this->breadcrumbs = array();
	}

	public function rewind() {
		reset($this->breadcrumbs);
    }

    public function current() {
		$current = current($this->breadcrumbs);
		return $current['url'];
    }

    public function key() {
		$current = current($this->breadcrumbs);
		return $current['name'];
    }

    public function next() {
		return next($this->breadcrumbs);
    }

    public function valid() {
        $key = key($this->breadcrumbs);
        return ($key !== NULL && $key !== FALSE);
    }

    public function count() {
        return sizeof($this->breadcrumbs);
    }

}