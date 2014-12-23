<?php
namespace LMMS;

/**
 * Extracted and adapted from KnpLabs/php-github-api, MIT licensed
 *
 * Copyright (c) 2012 KnpLabs
 * Copyright (c) 2010 Thibault Duplessis
 *
 */
class FilesystemCache
{
	/**
	* @var string
	*/
	protected $path;

	public function __construct($path)
	{
		$this->path = $path;
	}

	public function get($id)
	{
		if (false !== $content = @file_get_contents($this->getPath($id))) {
			return unserialize($content);
		}
		throw new \InvalidArgumentException(sprintf('File "%s" not found', $this->getPath($id)));
	}

	public function set($id, $content)
	{
		if (!is_dir($this->path)) {
			@mkdir($this->path, 0777, true);
		}
		if (false === @file_put_contents($this->getPath($id), serialize($content))) {
			throw new \InvalidArgumentException(sprintf('Cannot put content in file "%s"', $this->getPath($id)));
		}
	}

	public function has($id)
	{
		return file_exists($this->getPath($id));
	}

	public function getModifiedSince($id)
	{
		if ($this->has($id)) {
			return filemtime($this->getPath($id));
		}
	}
	public function getETag($id)
	{
		if (file_exists($this->getPath($id).'.etag')) {
			return file_get_contents($this->getPath($id).'.etag');
		}
	}
	/**
	* @param $id string
	*
	* @return string
	*/
	protected function getPath($id)
	{
		return sprintf('%s%s%s', rtrim($this->path, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR, md5($id));
	}
}
