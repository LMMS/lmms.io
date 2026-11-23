<?php
namespace App\Download;

enum Os: string
{
	case Unknown = "Unknown";
	case Linux = "Linux" ;
	case MacOS = "macOS";
	case Windows = "Windows";
}
