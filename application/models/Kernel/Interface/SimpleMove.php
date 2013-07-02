<?php
interface Application_Model_Kernel_Interface_SimpleMove {
	
	const TYPE_MOVE_UP = 1;
	const TYPE_MOVE_DOWN = 2;
	
	public function move($toId, $moveType);
}