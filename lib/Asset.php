<?php
namespace LMMS;

class Asset
{
	private string $metaData = '';
	public function __construct(
		private Platform $platform,
		private string $platformName,
		private string $releaseName,
		private string $downloadUrl,
		private ?string $description,
		private string $gitRef,
		private string $date,
	) {
		$_metaData = array();
		$_metaData['data-arch'] = $this->platform->architecture->name;
		$_metaData['data-os'] = $this->platform->os->name;
		$_metaData['data-qualifier'] = $this->platform->qualifier->name;
		$_metaData['data-osver'] = $this->platform->osVersion;

		foreach ($_metaData as $attr => $value) {
			$_value = strtolower($value);
			$this->metaData .= " $attr=\"$_value\"";
		}
	}

	public function getPlatform(): Platform
	{
		return $this->platform;
	}

	public function getPlatformName(): string
	{
		return $this->platformName;
	}

	public function getReleaseName(): string
	{
		return $this->releaseName;
	}

	public function getDownloadUrl(): string
	{
		return $this->downloadUrl;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function getGitRef(): string
	{
		return $this->gitRef;
	}

	public function getDate(): string
	{
		return $this->date;
	}

	public function getMetaData(): string
	{
		return $this->metaData;
	}
}
