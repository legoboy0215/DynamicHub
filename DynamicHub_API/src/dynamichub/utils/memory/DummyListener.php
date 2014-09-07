<?php

namespace dynamichub\event\memory;

class DummyListener implements ArrUpdateListener{
	public function onUpdate(Arr $arr){
		if($arr instanceof ChildArr){
			$arr->getParent()->getListener()->onUpdate($arr->getParent());
		}
	}
}
