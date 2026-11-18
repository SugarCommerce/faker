# Tests

`composer test` runs the PHPUnit suite. The site lookups' core functions are
stubbed in `bootstrap.php`; nothing else touches WordPress.

- **FakeTest** -- every generator, its shape and range, weights, and the seed.
- **MediaTest** -- demo files read the way a player reads them: a RIFF header
  that agrees with its length, a PNG signature, a one-page PDF with its fixed
  date, a ZIP walked to every member and its CRC, and the same bytes twice.
- **NoExitGuardTest** -- no source file ends the process when it is loaded.
