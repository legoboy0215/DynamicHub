<?php

namespace dynamichub\event\memory;

interface Value{
	public function getValue();
	public function setValue($value);
}
