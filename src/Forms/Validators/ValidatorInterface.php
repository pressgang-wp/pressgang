<?php

namespace PressGang\Forms\Validators;

interface ValidatorInterface {
	/**
	 * @return array<int, string>
	 */
	public function validate(): array;
}
