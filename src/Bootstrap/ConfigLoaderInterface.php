<?php

namespace PressGang\Bootstrap;

/**
 * Contract for configuration loaders. The default implementation is FileConfigLoader,
 * which reads PHP files from config/ directories; alternative implementations could
 * load config from a database or remote source.
 */
interface ConfigLoaderInterface {

	/**
	 * @return array<string, mixed>
	 */
	public function load(): array;
}
