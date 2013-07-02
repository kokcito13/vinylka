<?php
interface Application_Model_Kernel_Interface_Sort {

	/**
	 * @access public
	 * @return int
	 */
	public function next();

	/**
	 * @access public
	 * @return int
	 */
	public function prev();

	/**
	 * @access public
	 * @param int id
	 */
	public function interchange($id);

	/**
	 * @access public
	 * @return int
	 */
	public function getLastPosition();

	/**
	 * @access public
	 * @return int
	 */
	public function getFirstPosition();

}