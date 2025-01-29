<?php
namespace LMMS;
use LMMS\Platform;

enum Architecture: string {
    case Intel = "32-bit";
    case Intel64 = "Intel";
    case Arm = "ARM";
    case Arm64 = "ARM64";
    case Riscv = "RISC-V 32-bit";
    case Riscv64 = "RISC-V";
    case Ppc = "PowerPC 32-bit";
    case Ppc64 = "PowerPC";
    case Unknown = "Unknown";
}

enum Qualifier: string {
	case Mingw = "mingw";
	case Msvc = "msvc";
	case Gcc = "gcc";
	case Clang = "clang";
	case Vcpkg = "vcpkg";
	case Msys2 = "msys2";
	case Unknown = "Unknown";
}

class PlatformParser {
	const ARCHITECTURE_PATTERNS = [
		// 64 first: matches x86_64 before x86, etc
		Architecture::Intel64->value => ['x86_64', 'amd64', 'mingw64', 'win64'],
		Architecture::Intel->value => ['x86', 'i386', 'i686', 'mingw32', 'win32'],
		Architecture::Arm64->value => ['arm64', 'aarch64', 'armv8'],
		Architecture::Arm->value => ['arm', 'arm32', 'armv7'],
		Architecture::Riscv64->value => ['riscv64'],
		Architecture::Riscv->value => ['riscv'],
		Architecture::Ppc64->value => ['ppc64', 'powerpc64'],
		Architecture::Ppc->value => ['ppc', 'powerpc'],
	];

	const PLATFORM_PATTERNS = [
		Platform::Linux->value => ['linux', '.AppImage', '.run', '.rpm', '.deb'],
		Platform::Windows->value => ['windows', 'win32', 'win64', 'msys2', 'mingw32', 'mingw64', '.exe', 'msvc'],
		Platform::MacOS->value => ['mac', 'osx', '.pkg', '.dmg'],
	];

	const QUALIFIER_PATTERNS = [
		Qualifier::Mingw->value => ['mingw'],
		Qualifier::Msvc->value => ['msvc'],
		Qualifier::Clang->value => ['clang', 'llvm'],
		Qualifier::Vcpkg->value => ['vcpkg'],
		Qualifier::Msys2->value => ['msys2'],
		Qualifier::Gcc->value => ['gcc'],
	];

    private Architecture $architecture = Architecture::Unknown;
    private Platform $platform = Platform::Unknown;
    private Qualifier $qualifier = Qualifier::Unknown;
    private string $platformVersion = '';

    public function __construct(string $filename) {
		$filename = strtolower($filename);

		// Platform must be set first
		$this->platform = self::parse(self::PLATFORM_PATTERNS, $filename) ?: Platform::Unknown;

		// Determine architecture
		$this->architecture = self::parse(self::ARCHITECTURE_PATTERNS, $filename) ?:
			// Historically, macOS is Intel64 if we can't parse it
			($this->platform === Platform::MacOS ?
				Architecture::Intel64 : self::getDefaultArchitecture($this->platform));

		// Determine platform versions (macOS only)
		$this->platformVersion = self::getPlatformVersion($this->platform, $filename);

		$this->qualifier = self::parse(self::QUALIFIER_PATTERNS, $filename) ?: self::getDefaultQualifier($this->platform, $this->architecture);
    }

    private static function parse(array $patternsArray, string $filename) {
		foreach ($patternsArray as $key => $value) {
			foreach ($value as $pattern) {
				//echo $pattern . "\n";
				if (strpos($filename, $pattern) !== false) {
					switch(true) {
						case $patternsArray === self::PLATFORM_PATTERNS:
							return Platform::tryFrom($key);
						case $patternsArray === self::ARCHITECTURE_PATTERNS:
							return Architecture::tryFrom($key);
						case $patternsArray === self::QUALIFIER_PATTERNS:
							return Qualifier::tryFrom($key);
					}
				}
			}
		}
		return false;
    }

    private static function getDefaultArchitecture(Platform $platform): Architecture {
		switch($platform) {
			case Platform::Linux:
			case Platform::Windows:
				return Architecture::Intel64;
			case Platform::MacOS:
				return Architecture::Arm64;
			case Platform::Unknown:
				return Architecture::Unknown;
		}
    }

    private static function getDefaultQualifier(Platform $platform, Architecture $architecture): Qualifier {
		switch($platform) {
			case Platform::MacOS:
				return Qualifier::Clang;
			case Platform::Linux:
				return Qualifier::Gcc;
			case Platform::Windows:
				return $architecture === Architecture::Arm64 ? Qualifier::Msys2 : Qualifier::Mingw;
			case Platform::Unknown:
				return Qualifier::Unknown;
		}
    }

	/*
	 * Old macOS buttons had multiple OS versions, e.g. "10.11"
	 */
	private static function getPlatformVersion(Platform $platform, string $filename): string {
		if($platform === Platform::MacOS) {
			if (strpos($filename, '.dmg') !== false) {
				$parts = explode('-', explode('.dmg', $filename)[0]);
				return filter_var(array_pop($parts), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			}
		}
		return '';
	}

	public function getPlatform(): Platform {
		return $this->platform;
	}

    public function getArchitecture(): Architecture {
    	return $this->architecture;
    }

    public function getQualifier(): Qualifier {
    	return $this->qualifier;
    }

    private function isDefaultArchitecture(): bool {
    	return self::getDefaultArchitecture($this->platform) === $this->architecture;
    }

    private function isDefaultQualifier(): bool {
		return self::getDefaultQualifier($this->platform, $this->architecture) === $this->qualifier;
	}

	public function __toString(): string {
		$platform = $this->platform->value . ' ';
		$platformVersion = empty($this->platformVersion) ? '' : $this->platformVersion . ' ';
		$architecture = $this->isDefaultArchitecture() ? '' : $this->architecture->value . ' ';
		$qualifier = $this->isDefaultQualifier() ? '' : "(" . strtolower($this->qualifier->name) . ")";
		return trim($platform . $platformVersion . $architecture . $qualifier);
	}

	public function found() : bool {
		return $this->platform !== Platform::Unknown &&
			$this->architecture !== Architecture::Unknown;
	}
}

// Example usage:
// echo new PlatformParser("lmms-1.2.2-arm64.pkg");

