<?php
	// Debug Mode
		if ($_CONFIG['DEBUG'])      { error_reporting(E_ALL); }
		else                        { error_reporting(0); }

	// Timezone
		date_default_timezone_set($_CONFIG['TIMEZONE']);

	// INCLUDES / REQUIRE
		require __DIR__ . '/vendor/autoload.php';
		require __DIR__ . '/mysql.php';
		require __DIR__ . '/cache.php';
?>