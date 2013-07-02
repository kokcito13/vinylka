<?php
interface Application_Model_Kernel_Interface_Multilevel {

	public function getParentId();
	
	public function getChildrenNodes();
	
	public function getParentNode();

}