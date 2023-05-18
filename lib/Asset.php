<?php
namespace LMMS;

class Asset
{
	public function __construct(
		private Platform $platform,
		private string $platformName,
		private string $releaseName,
		private string $downloadUrl,
		private ?string $description,
		private string $gitRef,
		private string $date
	)
	{ }

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
}
