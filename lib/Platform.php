<?php
namespace LMMS;
use LMMS\Os;

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

class Platform {
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

	const OS_PATTERNS = [
		Os::Linux->value => ['linux', '.AppImage', '.run', '.rpm', '.deb'],
		Os::Windows->value => ['windows', 'win32', 'win64', 'msys2', 'mingw32', 'mingw64', '.exe', 'msvc'],
		Os::MacOS->value => ['mac', 'osx', '.pkg', '.dmg'],
	];

	const QUALIFIER_PATTERNS = [
		Qualifier::Mingw->value => ['mingw'],
		Qualifier::Msvc->value => ['msvc'],
		Qualifier::Clang->value => ['clang', 'llvm'],
		Qualifier::Vcpkg->value => ['vcpkg'],
		Qualifier::Msys2->value => ['msys2'],
		Qualifier::Gcc->value => ['gcc'],
	];

	public Architecture $architecture = Architecture::Unknown;
	public Os $os = Os::Unknown;
	public string $osVersion = '';
	public Qualifier $qualifier = Qualifier::Unknown;
	public string $platformVersion = '';

	public function __construct(string $filename) {
		$filename = strtolower($filename);

		// Platform must be set first
		$this->os = self::parse(self::OS_PATTERNS, $filename) ?: Os::Unknown;

		// Determine architecture
		$this->architecture = self::parse(self::ARCHITECTURE_PATTERNS, $filename) ?:
			// Historically, macOS is Intel64 if we can't parse it
			($this->os === Os::MacOS ?
				Architecture::Intel64 : self::getDefaultArchitecture($this->os));

		// Determine platform version (currently macOS only)
		$this->osVersion = self::getOsVersion($this->os, $filename);

		$this->qualifier = self::parse(self::QUALIFIER_PATTERNS, $filename) ?: self::getDefaultQualifier($this->os, $this->architecture);
	}

	private static function parse(array $patternsArray, string $filename) {
		foreach ($patternsArray as $key => $value) {
			foreach ($value as $pattern) {
				if (strpos($filename, $pattern) !== false) {
					switch(true) {
						case $patternsArray === self::OS_PATTERNS:
							return Os::tryFrom($key);
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

	private static function getDefaultArchitecture(Os $os): Architecture {
		switch($os) {
			case Os::Linux:
			case Os::Windows:
				return Architecture::Intel64;
			case Os::MacOS:
				return Architecture::Arm64;
			case Os::Unknown:
				return Architecture::Unknown;
		}
	}

	private static function getDefaultQualifier(Os $os, Architecture $architecture): Qualifier {
		switch($os) {
			case Os::MacOS:
				return Qualifier::Clang;
			case Os::Linux:
				return Qualifier::Gcc;
			case Os::Windows:
				return $architecture === Architecture::Arm64 ? Qualifier::Msys2 : Qualifier::Mingw;
			case Os::Unknown:
				return Qualifier::Unknown;
		}
	}

	// Parse os version information from filename for displaying os compatibility
	private static function getOsVersion(Os $os, string $filename): string {
		if($os === Os::MacOS) {
			if (strpos($filename, '.dmg') !== false) {
				$parts = explode('-', explode('.dmg', $filename)[0]);
				return filter_var(array_pop($parts), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			}
		}
		return '';
	}

	public function getOs(): Os {
		return $this->os;
	}

	public function getArchitecture(): Architecture {
		return $this->architecture;
	}

	public function getQualifier(): Qualifier {
		return $this->qualifier;
	}

	private function isDefaultArchitecture(): bool {
		return self::getDefaultArchitecture($this->os) === $this->architecture;
	}

	private function isDefaultQualifier(): bool {
		return self::getDefaultQualifier($this->os, $this->architecture) === $this->qualifier;
	}

	public function __toString(): string {
		$platform = $this->os->value . ' ';
		$platformVersion = empty($this->platformVersion) ? '' : $this->platformVersion . ' ';
		$architecture = $this->isDefaultArchitecture() ? '' : $this->architecture->value . ' ';

		$qualifiers = array();
		if($this->osVersion) {
		    array_push($qualifiers, strtolower($this->osVersion));
        }
        if(!$this->isDefaultQualifier()) {
            array_push($qualifiers, strtolower($this->qualifier->name));
    	}
        $qualifierText = count($qualifiers) ? ' (' . implode(', ', $qualifiers) . ")" : '';

		return trim($platform . $platformVersion . $architecture . $qualifierText);
	}

	public function found() : bool {
		return $this->os !== Os::Unknown &&
			$this->architecture !== Architecture::Unknown;
	}
}

// Example usage:
// echo new Platform("lmms-1.2.2-arm64.pkg"); // __toString()

